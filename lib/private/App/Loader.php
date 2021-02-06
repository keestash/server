<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published
 * by the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

namespace Keestash\App;

use Composer\Autoload\ClassLoader;
use doganoo\PHPAlgorithms\Datastructure\Cache\LRUCache;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\FileSystem\DirHandler;
use doganoo\PHPUtil\FileSystem\FileHandler;
use Keestash;
use Keestash\Exception\DuplicatedSameOrderException;
use KSP\App\IApp;
use KSP\App\ILoader;
use KSP\Core\Cache\ICacheService;
use KSP\Core\ILogger\ILogger;
use RecursiveDirectoryIterator;
use SplFileInfo;

/**
 * Class Loader
 * @package Keestash\App
 */
class Loader implements ILoader {

    private ClassLoader   $classLoader;
    private string        $appRoot;
    private HashTable     $apps;
    private LRUCache      $lruAppCache;
    private HashTable     $flushedApps;
    private ILogger       $logger;
    private ICacheService $cacheServer;

    public function __construct(
        ClassLoader $classLoader
        , ILogger $logger
        , ICacheService $cacheServer
        , string $appRoot
    ) {
        $this->classLoader = $classLoader;
        $this->appRoot     = $appRoot;
        $this->logger      = $logger;
        $this->apps        = new HashTable();
        $this->flushedApps = new HashTable();
        $this->lruAppCache = new LRUCache();
        $this->cacheServer = $cacheServer;
    }

    public function loadAppsAndFlush(): void {
        $this->loadApps();
        $this->flush();
    }

    public function loadApps(): void {
        $appRoot    = $this->appRoot . "apps/";
        $dirHandler = new DirHandler($appRoot);
        $result     = $dirHandler->list();

        foreach ($result as $appId => $value) {
            $this->loadApp($appId);
        }
    }

    public function loadApp(string $appId): bool {
        $app = new App();
        $app->setId($appId);
        $info = $this->loadInfo($app);

        if (false === $this->isValidInfo($info)) {
//            $this->logger->info("$appId is not installed properly. Ignoring");
            return false;
        }

        $disable = $info[IApp::FIELD_DISABLE] ?? false;

        if (false !== $disable || $disable === 1) {
//            $this->logger->info("$appId is disabled. Skipping!");
            return false;
        }

        $this->buildApp($info, $app);
        $this->overrideDefaultApp($app);
        $this->registerForScss($app);
        $this->apps->put($app->getId(), $app);

        return true;
    }

    private function loadInfo(IApp $app): ?array {
        $file = "{$this->appRoot}/apps/{$app->getId()}/info/info.json";

        if ($this->cacheServer->exists($file)) {
            return json_decode($this->cacheServer->get($file), true);
        }

        if (is_file($file) && is_readable($file)) {
            $content = file_get_contents($file);
            $decoded = json_decode(
                $content
                , true
            );
            $this->cacheServer->set($file, $content);
            return $decoded;
        }
        return null;
    }

    private function isValidInfo(?array $info): bool {
        if (null === $info) return false;

        $id             = $info[IApp::FIELD_ID] ?? null;
        $order          = (int) ($info[IApp::FIELD_ORDER] ?? null);
        $namespace      = $info[IApp::FIELD_NAMESPACE] ?? null;
        $name           = $info[IApp::FIELD_NAME] ?? null;
        $baseRoot       = $info[IApp::FIELD_BASE_ROUTE] ?? null;
        $faIconClass    = $info[IApp::FIELD_FA_ICON_CLASS] ?? null;
        $version        = (int) ($info[IApp::FIELD_VERSION] ?? null);
        $versionString  = $info[IApp::FIELD_VERSION_STRING] ?? null;
        $type           = $info[IApp::FIELD_TYPE] ?? null;
        $backgroundJobs = $info[IApp::FIELD_BACKGROUND_JOBS] ?? null;

        if (null === $id) return false;
        if (null === $namespace) return false;
        if (null === $name) return false;
        if (null === $baseRoot) return false;
        if (null === $faIconClass) return false;
        if ($order <= 0) return false;
        if ($version <= 0) return false;
        if (null === $versionString) return false;
        if (null === $type) return false;

        if (null !== $backgroundJobs) {

            if (false === is_array($backgroundJobs)) return false;

            foreach ($backgroundJobs as $job) {
                $interval = $job[IApp::FIELD_BACKGROUND_JOBS_INTERVAL] ?? null;
                $type     = $job[IApp::FIELD_BACKGROUND_JOBS_TYPE] ?? null;
                if (null === $interval) return false;
                if (null === $type) return false;
            }

        }
        return true;
    }

    private function buildApp(array $info, IApp $app): void {
        /** @var App $app */
        $app->setOrder((int) $info[IApp::FIELD_ORDER]);
        $app->setName($info[IApp::FIELD_NAME]);
        $app->setNamespace($info[IApp::FIELD_NAMESPACE]);
        $app->setBaseRoute($info[IApp::FIELD_BASE_ROUTE]);
        $app->setAppPath("{$this->appRoot}apps/{$app->getId()}");
        $app->setTemplatePath("{$app->getAppPath()}/template/");
        $app->setStringPath("{$app->getAppPath()}/string/");
        $app->setFAIconClass($info[IApp::FIELD_FA_ICON_CLASS]);
        $app->setVersion((int) $info[IApp::FIELD_VERSION]);
        $app->setVersionString($info[IApp::FIELD_VERSION_STRING]);
        $app->setType($info[IApp::FIELD_TYPE]);
        $showIcon = $info[IApp::FIELD_SHOW_ICON] ?? 0;
        $app->setShowIcon($showIcon === 1);
        $app->setBackgroundJobs($info[IApp::FIELD_BACKGROUND_JOBS] ?? []);
    }

    private function overrideDefaultApp(IApp $app): void {
        $currentAppKey = $this->lruAppCache->last();
        /** @var IApp|null $currentApp */
        $currentApp = $this->lruAppCache->get($currentAppKey);

        if (null === $currentApp) {
            $this->lruAppCache->put($app->getId(), $app);
            return;
        }

        if ($app->getOrder() === $currentApp->getOrder() && $app->getId() !== $currentApp->getId()) {
            throw new DuplicatedSameOrderException("there are two apps with the same order ({$app->getName()} and {$currentApp->getName()})");
        }
        if ($app->getOrder() < $currentApp->getOrder()) {
            $this->lruAppCache->put($app->getId(), $app);
        }
    }

    private function registerForScss(IApp $app): void {
        $source      = $app->getAppPath() . "/scss/";
        $destination = $app->getAppPath() . "/scss/dist/";

        if (false === is_dir($source) || false === is_dir($destination)) {
//            $this->logger->warning("$source or $destination is not a directory. Can not register scss");
            return;
        }

        Keestash::getServer()
            ->getStylesheetManager()
            ->register($app);

    }

    public function flush(): void {
        foreach ($this->apps->keySet() as $key) {
            /** @var IApp $app */
            $app = $this->apps->get($key);

            if (true === $this->flushedApps->containsKey($app->getId())) {
                continue;
            }

            $this->buildNamespaceAndRequire($app);
            $this->requireInfoPhp($app);
            $this->loadTemplate($app);
            $this->loadString($app);
//        $this->loadJs($app);
            $this->flushedApps->put($app->getId(), $app);
        }
    }

    private function buildNamespaceAndRequire(IApp $app): bool {
        $this->classLoader->setPsr4(
            "KSA\\{$app->getNamespace()}\\"
            , "{$this->appRoot}/apps/{$app->getId()}/lib/"
        );
        return true;
    }

    private function requireInfoPhp(IApp $app): bool {
        $file        = "{$this->appRoot}/apps/{$app->getId()}/info/info.php";
        $fileHandler = new FileHandler($file);
        if (true === $fileHandler->isReadable()) {
            /** @noinspection PhpIncludeInspection */
            require_once $file;
            return true;
        }

        return false;
    }

    private function loadTemplate(IApp $app) {
        $dir = $app->getTemplatePath();
        if (false === is_dir($dir)) return;
        $iterator = new RecursiveDirectoryIterator($dir);

        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (false === $info->isDir()) continue;
            $path = $info->getRealPath();

            if (ILoader::DIR_NAME_FRONTEND === $info->getBasename()) {
                Keestash::getServer()
                    ->getFrontendTemplateManager()
                    ->addPath(
                        $path
                    );
            } else {
                Keestash::getServer()->getTemplateManager()
                    ->addPath(
                        $path
                    );

            }

        }

    }

    private function loadString(IApp $app) {

        $dir = $app->getStringPath();
        if (false === is_dir($dir)) return;
        $dirsToAdd   = [];
        $dirsToAdd[] = $dir;
        $iterator    = new RecursiveDirectoryIterator($dir);

        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (false === $info->isDir()) continue;

            if (ILoader::DIR_NAME_FRONTEND === $info->getBasename()) {
                Keestash::getServer()
                    ->getFrontendStringManager()
                    ->addPath(
                        $app->getId()
                        , $info->getRealPath()
                    );
            }

        }

    }

    public function loadCoreAppsAndFlush(): void {
        $this->loadCoreApps();
        $this->flush();
    }

    public function loadCoreApps(): void {
        $coreApps = [
            ILoader::APP_NAME_ABOUT
            , ILoader::APP_NAME_ACCOUNT
            , ILoader::APP_NAME_APPS
            , ILoader::APP_NAME_FORGOT_PASSWORD
            , ILoader::APP_NAME_GENERAL_API
            , ILoader::APP_NAME_GENERAL_VIEW
            , ILoader::APP_NAME_INSTALL
            , ILoader::APP_NAME_INSTALL_INSTANCE
            , ILoader::APP_NAME_LOGIN
            , ILoader::APP_NAME_LOGOUT
            , ILoader::APP_NAME_MAINTENANCE
            , ILoader::APP_NAME_PROMOTION
            , ILoader::APP_NAME_REGISTER
            , ILoader::APP_NAME_TNC
            , ILoader::APP_NAME_USERS
        ];

        foreach ($coreApps as $coreApp) {
            $this->loadApp($coreApp);
        }
    }

    public function getApps(): HashTable {
        return $this->apps;
    }

    public function unloadApp(string $key): bool {
        // TODO we need to make sure that all the namespaces
        //  and so one are also unloaded.
        //  Maybe, we can move the load/unload stuff before we really
        //  load all apps (something like "flush").
        $cacheClearead = $this->lruAppCache->delete($key);
        $appsCleared   = $this->apps->remove($key);
        return $cacheClearead && $appsCleared;
    }

    public function getDefaultApp(): ?IApp {
        return $this->lruAppCache->get(
            $this->lruAppCache->last()
        );
    }

    public function hasApp(string $name): bool {
        if (null === $this->apps) return false;
        return $this->apps->containsKey($name);
    }

    private function loadJs(IApp $app): bool {
        if (Keestash::MODE_WEB !== Keestash::getMode()) return false;
        $dirHandler = new DirHandler(
            $app->getAppPath() . "/js/dist/"
        );
        foreach ($dirHandler->list() as $item) {
            if (false === (substr_compare($item, ".bundle.js", -strlen(".bundle.js")) === 0)) continue;
            Keestash::getServer()->getTemplateManager()->addAppScript(
                "apps/{$app->getName()}/js/dist/$item"
                , $app->getBaseRoute()
                , "application/javascript"
            );
        }

        return true;
    }

}

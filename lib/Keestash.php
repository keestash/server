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

use DI\DependencyException;
use DI\NotFoundException;
use doganoo\DI\HTTP\IHTTPService;
use Keestash\Api\Response\MaintenanceResponse;
use Keestash\Api\Response\NeedsUpgrade;
use Keestash\Api\Response\SessionExpired;
use Keestash\App\Config\Diff;
use Keestash\App\Helper;
use Keestash\Command\KeestashCommand;
use Keestash\Core\Manager\NavigationManager\App\NavigationManager as AppNavigationManager;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use Keestash\Core\Manager\RouterManager\Route;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Instance\MaintenanceService;
use Keestash\Core\Service\Router\Installation\App\InstallAppService;
use Keestash\Core\Service\Router\Installation\Instance\InstallInstanceService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Server;
use Keestash\View\Navigation\Entry;
use Keestash\View\Navigation\Part;
use KSP\Api\IResponse;
use KSP\App\IApp;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\HTTP\Route\IRouteService;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IBag;
use Symfony\Component\Console\Application;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class Keestash
 *
 */
class Keestash {

    public const MODE_NONE    = 0;
    public const MODE_WEB     = 1;
    public const MODE_API     = 2;
    public const MODE_CONSOLE = 3;

    private static ?Server $server = null;
    private static int     $mode   = Keestash::MODE_NONE;

    private function __construct() {

    }

    /**
     * @return bool
     * @throws DependencyException
     * @throws NotFoundException
     */
    public static function requestWeb(): bool {
        Keestash::$mode = Keestash::MODE_WEB;
        Keestash::initRequest();

        /** @var PersistenceService $persistenceService */
        $persistenceService = Keestash::getServer()->query(PersistenceService::class);
        $logger             = Keestash::getServer()->getFileLogger();

        try {
            $persisted = $persistenceService->isPersisted("user_id");
        } catch (Throwable $exception) {
            $logger->error('error during persistence request ' . $exception->getMessage() . ': ' . $exception->getTraceAsString());
            $persisted = false;
        }

        $router = Keestash::getServer()->getHTTPRouter();

        if (true === $persisted || $router->isPublicRoute()) {
            $router->route(null);
        } else {
            $router->routeTo("login");
        }

        Keestash::renderTemplates();
        Keestash::flushOutput();
        return true;
    }

    private static function initRequest() {
        Keestash::init();

        set_time_limit(0);
        session_set_save_handler(
            Keestash::getServer()->query(SessionHandlerInterface::class)
        );
        // step3 has to be: is configured!
        //      this contains stuff like is writable,
        //      has file permissions, etc
        //step 3: init the handler
        Keestash::initHandler();

        // step 4.1: we can do this only
        // after all apps are loaded :(
        Keestash::replaceDefaultAppURL();

        // step 5: flush output
        Keestash::flushOutput();
    }

    public static function init() {
        //step 1: init the basic app
        Keestash::initApp();

        //step 4: load apps
        Keestash::getServer()
            ->getAppLoader()
            ->loadCoreAppsAndFlush();

        //step 2: check for is installed
        Keestash::isInstanceInstalled();

        Keestash::areAppsInstalled();

        //step 4: load apps
        Keestash::getServer()
            ->getAppLoader()
            ->loadApps();

        //step 3: check for maintenance
        Keestash::isMaintenanceMode();

        Keestash::getServer()
            ->getAppLoader()
            ->flush();

    }

    private static function initApp(): bool {
        $appRoot = self::getAppRoot();

        $vendorAutoLoad = $appRoot . "vendor/autoload.php";

        /** @var Composer\Autoload\ClassLoader $classLoader */
        /** @noinspection PhpIncludeInspection */
        $classLoader = require $vendorAutoLoad;

        self::$server = new Server(
            $appRoot
            , $classLoader
        );

        Keestash::getServer()->getSystem()->createConfig();
        return true;
    }

    private static function getAppRoot(): string {
        return str_replace("\\", '/', substr(__DIR__, 0, -3));
    }

    public static function getServer(): Server {
        return Keestash::$server;
    }

    private static function isInstanceInstalled(): void {
        /** @var InstallInstanceService $installInstanceService */
        $installInstanceService = Keestash::getServer()->query(InstallInstanceService::class);
        /** @var HTTPService $httpService */
        $httpService          = Keestash::getServer()->query(HTTPService::class);
        $lockHandler          = Keestash::getServer()->getInstanceLockHandler();
        $instanceDB           = Keestash::getServer()->getInstanceDB();
        $logger               = Keestash::getServer()->getFileLogger();
        $instanceHash         = $instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        $instanceId           = $instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_ID);
        $isLocked             = $lockHandler->isLocked();
        $routesToInstallation = $installInstanceService->routesToInstallation();

        $logger->debug("isLocked: " . (string) $isLocked);
        $logger->debug("routesToInstallation: " . (string) $routesToInstallation);
        if (true === $isLocked && true === $routesToInstallation) return;

        if ((null === $instanceHash || null === $instanceId)) {
            $logger->debug("The whole application is not installed. Please Install");
            $lockHandler->lock();
            $httpService->routeToInstallInstance();
            exit();
            die();
        }

    }

    private static function areAppsInstalled(): void {
        $instanceLockHandler = Keestash::getServer()->getInstanceLockHandler();
        $instanceLocked      = $instanceLockHandler->isLocked();

        // if we are actually installing the instance,
        // we need to make sure that Keestash does not want
        // to Install the apps
        if (true === $instanceLocked) return;

        // We only check loadedApps if the system is
        // installed
        $loadedApps    = Keestash::getServer()->getAppLoader()->getApps();
        $installedApps = Keestash::getServer()->getAppRepository()->getAllApps();

        $diff          = new Diff();
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 1: we check if we have new apps to Install
        if ($appsToInstall->size() > 0) {
            Keestash::handleNeedsUpgrade();
        }

        // Step 2: we remove all apps that are disabled in our db
        $loadedApps = $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        if ($appsToUpgrade->size() > 0) {
            Keestash::handleNeedsUpgrade();
        }
    }

    private static function handleNeedsUpgrade(): void {
        /** @var InstallAppService $installationService */
        $installationService  = Keestash::getServer()->query(InstallAppService::class);
        $logger               = Keestash::getServer()->getFileLogger();
        $lockHandler          = Keestash::getServer()->getAppLockHandler();
        $appsLocked           = $lockHandler->isLocked();
        $routesToInstallation = $installationService->routesToInstallation();

        if (true === $appsLocked && true === $routesToInstallation) {
            $logger->debug("already locked. Do not test again!");
            return;
        }

        if (Keestash::getMode() === Keestash::MODE_WEB) {
            $lockHandler->lock();
            // in this case, we redirect to the Install page
            // since the user is logged in and is in web mode
            Keestash::getServer()
                ->getHTTPRouter()
                ->routeTo("install");
            exit();
            die();
        }

        if (Keestash::getMode() === Keestash::MODE_API && false === $routesToInstallation) {
            // in all other cases, we simply return an
            // "need to upgrade" JSON String (except for the
            // case where we already route to installation)
            self::getServer()->getResponseManager()->add(
                new NeedsUpgrade(
                    Keestash::getServer()->getL10N()
                )
            );
        }
    }

    public static function getMode(): int {
        return self::$mode;
    }

    private static function isMaintenanceMode(): void {
        /** @var MaintenanceService $maintenanceService */
        $maintenanceService = Keestash::getServer()->query(MaintenanceService::class);
        $isMaintenance      = $maintenanceService->isMaintenance();

        Keestash::handleApiMaintenance($isMaintenance);
        Keestash::handleHttpMaintenance($isMaintenance);
    }

    private static function handleApiMaintenance(bool $isMaintenance): void {
        if (Keestash::getMode() !== Keestash::MODE_API) return;

        if (true === $isMaintenance) {
            self::getServer()->getResponseManager()->unsetResponses();
            self::getServer()->getResponseManager()->add(
                self::getServer()->query(MaintenanceResponse::class)
            );
            exit();
            die();
        }

    }

    private static function handleHttpMaintenance(bool $isMaintenance): void {
        if (self::getMode() !== Keestash::MODE_WEB) return;
        if (false === $isMaintenance) return;

        /** @var HTTPRouter $router */
        $router    = self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER);
        $routeName = $router->getRouteName();
        if ($routeName !== "maintenance") {
            $router->routeTo("maintenance");
            exit();
            die();
        }
    }

    private static function initHandler(): void {
        Keestash::setExceptionHandler();
        Keestash::initDevHandler();
        Keestash::initProductionHandler();
    }

    private static function setExceptionHandler(): void {
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $logger        = Keestash::getServer()->getFileLogger();
        $showErrors    = $configService->getValue("show_errors", false);
        $debug         = $configService->getValue("debug", false);

        if (
            Keestash::$mode === Keestash::MODE_WEB
            && true === $showErrors
            && true === $debug
        ) {
            return;
        }

        set_error_handler(
            function (int $id
                , string $message
                , string $file
                , int $line
                , array $context
            ) use ($logger) {

                $logger->error(
                    json_encode(
                        [
                            "id"                => $id
                            , "message"         => $message
                            , "file"            => $file
                            , "line"            => $line
                            , "context"         => $context
                            , "debug_backtrace" => debug_backtrace()
                        ]
                    ),
                    $context
                );

            });

        set_exception_handler(
            function (Throwable $exception) use ($logger): void {

                $logger->error(
                    json_encode(
                        [
                            "id"                => $exception->getCode()
                            , "message"         => $exception->getMessage()
                            , "file"            => $exception->getFile()
                            , "line"            => $exception->getLine()
                            , "trace"           => json_encode($exception->getTrace())
                            , "trace_as_string" => $exception->getTraceAsString()
                        ]
                    )
                );

            }
        );
    }

    private static function initDevHandler(): void {
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $isDebug       = $configService->getValue("debug", false);
        if (false === $isDebug) return;
        Keestash::installWhoops();
    }

    private static function installWhoops(): void {
        if (self::$mode === Keestash::MODE_API) return;
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $showErrors    = $configService->getValue("show_errors", false);

        if (false === $showErrors) return;

        $whoops = new Run();
        $whoops->pushHandler(new PrettyPageHandler());
        $whoops->register();

    }

    private static function initProductionHandler(): void {
        if (self::$mode === Keestash::MODE_API) return;
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $isDebug       = $configService->getValue("debug", false);

        if (true === $isDebug) return;
        Keestash::hideErrors();
        Keestash::hideOutput();
    }

    private static function hideErrors() {
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $isDebug       = $configService->getValue("debug", false);
        $showErrors    = $configService->getValue("show_errors", false);

        error_reporting(E_ALL | E_STRICT);
        @ini_set('display_errors', $showErrors && $isDebug ? '1' : '0');
        @ini_set('log_errors', $showErrors && $isDebug ? '1' : '0');
    }

    private static function hideOutput() {
//        ob_start();
    }

    /**
     * We can do this only after loading all apps - kinda EXTRAWURST
     */
    private static function replaceDefaultAppURL(): void {
        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::NAV_BAR
            , [
                "mainHref" => Helper::getDefaultURL()
            ]
        );
    }

    private static function flushOutput(callable $callable = null) {
        /** @var ConfigService $configService */
        $configService = Keestash::getServer()->query(ConfigService::class);
        $isDebug       = $configService->getValue("debug", false);

        if (true === $isDebug) return;
        if (null !== $callable) $callable(ob_get_contents());
//        ob_end_clean();
    }

    private static function renderTemplates() {
        Keestash::renderWebTemplates();
        Keestash::renderApiTemplates();
        Keestash::renderConsoleTemplates();
    }

    private static function renderWebTemplates() {
        if (Keestash::getMode() !== Keestash::MODE_WEB) return;
        Keestash::loadTemplates();
        Keestash::initTemplates();
        Keestash::addTopNavigation();

        $legacy = self::getServer()->getLegacy();

        if (!self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->isPublicRoute()) {

            Keestash::getServer()
                ->getTemplateManager()
                ->replace(
                    ITemplate::NAV_BAR
                    , [
                        "vendorName" => $legacy->getApplication()->get("name")
                        , "settings" => Keestash::getServer()
                            ->getNavigationManager()
                            ->getByName(NavigationManager::NAVIGATION_TYPE_SETTINGS)
                            ->getAll()
                        , "menu"     => Keestash::getServer()->getL10N()->translate("Menu")
                        , "baseURL"  => Keestash::getBaseURL()
                    ]
                );
        }

        $navigation = Keestash::getServer()->getTemplateManager()->render(ITemplate::NAV_BAR);
        $appContent = Keestash::getServer()->getTemplateManager()->render(ITemplate::APP_CONTENT);

        $routeName = Keestash::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteName();
        /** @var IRouteService $routeService */
        $routeService = Keestash::getServer()->query(IRouteService::class);
        $appId        = $routeService->routeToAppId($routeName);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::HEAD
            , [
            "appStyleSheet" => Keestash::getServer()->getStylesheetManager()->get($routeName)
            , "scripts"     => Keestash::getServer()->getTemplateManager()->getScripts()
            , "appScripts"  => Keestash::getServer()->getTemplateManager()->getAppScripts($appId)
        ]);

        /** @var AppNavigationManager $appNavigationManager */
        $appNavigationManager = Keestash::getServer()->query(AppNavigationManager::class);
        $hasAppNavigation     =
            (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER)
            && $appNavigationManager->getList()->length() > 0;
        $staticController     = (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER_STATIC);
        $contextLess          = (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER_CONTEXTLESS);
        $actionBar            = Keestash::renderActionBars(
            Keestash::getServer()->getActionBarManager()->get(IBag::ACTION_BAR_TOP)
        );
        $head                 = Keestash::getServer()->getTemplateManager()->render(ITemplate::HEAD);

        if (!self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->isPublicRoute()) {
            Keestash::getServer()->getTemplateManager()->replace(
                ITemplate::APP_NAVIGATION
                , [
                    "appNavigation"      => $appNavigationManager->getList()->toArray(false)
                    , "hasAppNavigation" => $hasAppNavigation
                    , "actionBar"        => $actionBar
                    , "hasActionBars"    => Keestash::getServer()->getActionBarManager()->isVisible()
                ]
            );
        }
        $appNavigation = Keestash::getServer()->getTemplateManager()->render(ITemplate::APP_NAVIGATION);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::CONTENT
            , [
                "appNavigation"      => $appNavigation
                , "appContent"       => $appContent
                , "hasAppNavigation" => $hasAppNavigation
                , "hasBreadcrumbs"   => false
            ]
        );

        $content = Keestash::getServer()->getTemplateManager()->render(ITemplate::CONTENT);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::FOOTER
            , [
                "start_year"     => $legacy->getApplication()->get("start_date")->format("Y")
                , "current_year" => (new DateTime())->format("Y")
                , "appName"      => $legacy->getApplication()->get("name")
                , "vendor_name"  => $legacy->getVendor()->get("name")
                , "vendor_url"   => $legacy->getVendor()->get("web")
            ]
        );
        $footer = Keestash::getServer()->getTemplateManager()->render(ITemplate::FOOTER);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::BODY
            , [
                "navigation"      => $navigation
                , "content"       => $content
                , "noContext"     => $contextLess
                , "staticContext" => $staticController
                , "footer"        => $footer
            ]
        );

        $body = Keestash::getServer()->getTemplateManager()->render(ITemplate::BODY);

        $partTemplate = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::PART_TEMPLATE);
        $sideBar      = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::SIDE_BAR);
        /** @var ILocaleService $localeService */
        $localeService = Keestash::getServer()->query(ILocaleService::class);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::HTML
            , [
                "head"              => $head
                , "host"            => Keestash::getBaseURL()
                , "apiHost"         => Keestash::getBaseAPIURL()
                , "body"            => $body
                , "noContext"       => $staticController
                , "partTemplate"    => $partTemplate
                , "sidebarTemplate" => $sideBar
                , "language"        => $localeService->getLocale()
            ]
        );
        $html = Keestash::getServer()->getTemplateManager()->render(ITemplate::HTML);
        echo $html;
    }

    private static function loadTemplates() {
        $appRoot      = Keestash::getAppRoot();
        $templatePath = null;
        $frontendPath = null;

        Keestash::getServer()
            ->getFrontendTemplateManager()
            ->addPath(
                realpath("$appRoot/template/app/frontend")
            );

        $templatePath     = $appRoot . "/template/app";
        $frontendPath     = $templatePath . "/frontend";
        $mailTemplatePath = $appRoot . "/template/email";

        Keestash::getServer()
            ->getTemplateManager()
            ->addPath($templatePath);

        // TODO exclude to a own manager
        Keestash::getServer()
            ->getTemplateManager()
            ->addPath($mailTemplatePath);

        Keestash::getServer()
            ->getFrontendTemplateManager()
            ->addPath($frontendPath);

    }

    private static function initTemplates(): void {
        if (self::$mode === Keestash::MODE_API) return;

        $legacy    = self::getServer()->getLegacy();
        $userImage = null;

        /** @var IFileManager $fileManager */
        $fileManager = Keestash::getServer()->query(IFileManager::class);
        /** @var RawFileService $rawFileService */
        $rawFileService = Keestash::getServer()->query(RawFileService::class);
        /** @var FileService $fileService */
        $fileService = Keestash::getServer()->query(FileService::class);

        $instanceLockHandler = Keestash::getServer()->getInstanceLockHandler();
        $instanceLocked      = $instanceLockHandler->isLocked();
        $contextLess         = (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER_CONTEXTLESS);

        // we are not interested in a profile image when
        // we are installing the instance or will not show
        if (false === $instanceLocked && false === $contextLess) {
            $file = $fileManager->read(
                $rawFileService->stringToUri(
                    $fileService->getProfileImagePath(Keestash::getServer()->getUserFromSession())
                )
            );

            if (null === $file) {
                $file = $fileService->getDefaultImage();
            }

            // TODO hotfix
            //  we need to fix that fullpath stuff, where extension
            //  is sometimes part of the path and sometimes not
            $path = $file->getFullPath();
            if (false === is_file($path)) {
                $path = "{$file->getDirectory()}/{$file->getName()}";
            }
            $userImage = $rawFileService->stringToBase64($path);
        }

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::NAV_BAR
            , [
            "logopath"      => Keestash::getBaseURL(false) . "/asset/img/logo_no_name.png"
            , "logoutURL"   => Keestash::getBaseURL() . "logout"
            , "userImage"   => $userImage
            , 'contextless' => $contextLess
        ]);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::HEAD
            , [
            "title"            => $legacy->getApplication()->get("name")
            , "stylecss"       => self::getBaseURL(false) . "/lib/scss/dist/style.css"
            , "faviconPath"    => self::getBaseURL(false) . "/asset/img/favicon.png"
            , "fontAwesomeCss" => "https://use.fontawesome.com/releases/v5.5.0/css/all.css"
            , "baseJs"         => self::getBaseURL(false) . "lib/js/dist/base.bundle.js"
        ]);

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::NO_CONTENT
            , [
                "noContentAvailable" => Keestash::getServer()->getL10N()->translate("No Content Available")
            ]
        );
    }

    /**
     * have a look here: https://stackoverflow.com/questions/6768793/get-the-full-url-in-php
     *
     * @param bool $withScript
     * @param bool $forceIndex
     *
     * @return string
     */
    public static function getBaseURL(bool $withScript = true, bool $forceIndex = false): ?string {
        if (true === in_array(Keestash::getMode(), [Keestash::MODE_NONE, Keestash::MODE_CONSOLE], true)) return null;
        $scriptName          = "index.php";
        $scriptNameToReplace = $scriptName;
        if (self::$mode === self::MODE_API) {
            $scriptName          = "api.php";
            $scriptNameToReplace = $scriptName;
        }
        if (true === $forceIndex) {
            $scriptNameToReplace = "index.php";
        }

        $url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $position = strpos($url, $scriptName);
        $position = false === $position ? 0 : $position;

        if ($withScript) {
            return substr($url, 0, $position) . $scriptNameToReplace;
        } else {
            return substr($url, 0, $position) . "";
        }
    }

    private static function addTopNavigation(): void {
        if (self::getMode() !== Keestash::MODE_WEB) return;
        $apps = Keestash::getServer()->getAppLoader()->getApps();

        $part = new Part();
        foreach ($apps->keySet() as $key) {

            /** @var IApp $app */
            $app = $apps->get($key);

            $entry = new Entry();
            $entry->setName($app->getName());
            $entry->setId($app->getId());
            $entry->setFaClass($app->getFAIconClass());
            $entry->setOrder((int) $app->getOrder());
            $entry->setVisible($app->showIcon());

            $part->addEntry($entry);
        }
        $part->getEntries()->sort();
        Keestash::getServer()->getNavigationManager()->addPart(NavigationManager::NAVIGATION_TYPE_TOP, $part);
    }

    private static function renderActionBars(IBag $actionBarBag): string {
        $rendered = "";

        /** @var IActionBar $actionBar */
        foreach ($actionBarBag->getAll() as $actionBar) {
            if (false === $actionBar->hasElements()) continue;
            Keestash::getServer()->getTemplateManager()->replace(
                ITemplate::ACTION_BAR
                , [
                    "actionBar" => $actionBar
                ]
            );
            $rendered = $rendered . Keestash::getServer()->getTemplateManager()->render(ITemplate::ACTION_BAR);
        }
        return $rendered;
    }

    private static function getBaseAPIURL(): ?string {
        $baseURL = Keestash::getBaseURL(true, false);
        if (null === $baseURL) return null;
        return str_replace("index.php", "api.php", $baseURL);
    }

    private static function renderApiTemplates() {
        if (self::$mode !== Keestash::MODE_API) return;
        Keestash::loadTemplates();
    }

    private static function renderConsoleTemplates() {
        if (self::$mode !== Keestash::MODE_CONSOLE) return;
        Keestash::loadTemplates();
    }

    public static function requestConsole(): void {
        Keestash::$mode = Keestash::MODE_CONSOLE;
        Keestash::initRequest();
        Keestash::renderTemplates();

        $consoleManager = Keestash::getServer()->getConsoleManager();
        $commands       = $consoleManager->getSet();
        $cliVersion     = "1.0.0";

        $application = new Application(
            Keestash::getServer()->getLegacy()->getApplication()->get("name") . " CLI Tools"
            , $cliVersion
        );

        /** @var KeestashCommand $command */
        foreach ($commands->getCommands() as $command) {
            $application->add($command);
        }

        $application->run();

    }

    public static function requestApi(): void {
        Keestash::$mode = Keestash::MODE_API;
        Keestash::initRequest();
        Keestash::renderTemplates();

        /** @var APIRouter $router */
        $router    = Keestash::getServer()->getRouterManager()->get(IRouterManager::API_ROUTER);
        $parameter = $router->getRequiredParameter();

        /** @var Verification $verification */
        $verification = self::getServer()->query(Verification::class);
        $token        = $verification->verifyToken($parameter);

        if (null !== $token || $router->isPublicRoute()) {
            $router->route($token);
        } else {

            self::getServer()->getResponseManager()->add(
                new SessionExpired(
                    Keestash::getServer()->getL10N()
                )
            );

        }

        Keestash::validateApi();
        Keestash::flushOutput();
    }

    private static function validateApi() {
        $responses = Keestash::getServer()->getResponseManager()->getResponses();
        /** @var IResponse $response */
        $response = $responses->get(0);

        /** @var IHTTPService $httpService */
        $httpService = Keestash::getServer()->query(IHTTPService::class);
        $code        = $response->getCode();
        $description = $httpService->translateCode($code);

        header('Access-Control-Allow-Origin: *');
        header("HTTP/1.1 $code $description");

        foreach ($response->getHeaders() as $key => $header) {
            header("$key: $header");
        }
        echo $response->getMessage();

    }

}

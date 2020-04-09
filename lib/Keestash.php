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

use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Log\Logger;
use Keestash\Api\Response\MaintenanceResponse;
use Keestash\Api\Response\NeedsUpgrade;
use Keestash\Api\Response\SessionExpired;
use Keestash\App\Config\Diff;
use Keestash\App\Helper;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use Keestash\Core\Manager\RouterManager\Route;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\Helper as RouterHelper;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\MaintenanceService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\System\Installation\LockHandler;
use Keestash\Exception\KeestashException;
use Keestash\Server;
use Keestash\View\Navigation\Entry;
use Keestash\View\Navigation\Navigation;
use Keestash\View\Navigation\Part;
use KSP\Api\IResponse;
use KSP\App\IApp;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarBag;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

/**
 * Class Keestash
 *
 */
class Keestash {

    public const MODE_NONE = 0;
    public const MODE_WEB  = 1;
    public const MODE_API  = 2;
    /** @var Server $server */
    private static $server = null;
    private static $mode   = Keestash::MODE_NONE;

    private function __construct() {

    }

    /**
     * @return bool
     */
    public static function requestWeb(): bool {
        Keestash::$mode = Keestash::MODE_WEB;
        Keestash::initRequest();

        /** @var PersistenceService $persistenceService */
        $persistenceService = Keestash::getServer()->query(PersistenceService::class);
        $persisted          = $persistenceService->isPersisted("user_id");

        /** @var HTTPRouter $router */
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

        $logPath  = Keestash::getServer()->getLogfilePath();
        $logLevel = Keestash::getServer()->getConfig()->get("log_level");

        FileLogger::setPath($logPath);
        Logger::setLogLevel((int) $logLevel);

        return true;
    }

    private static function getAppRoot(): string {
        return str_replace("\\", '/', substr(__DIR__, 0, -3));
    }

    public static function getServer(): Server {
        return self::$server;
    }

    /**
     * TODO exclude frontend paths in sub folder 'frontend'
     */
    private static function loadTemplates() {
        $appRoot      = Keestash::getAppRoot();
        $mode         = Keestash::getMode();
        $templatePath = null;
        $frontendPath = null;

        Keestash::getServer()
            ->getFrontendTemplateManager()
            ->addPath(
                realpath("$appRoot/template/app/frontend")
            );

        switch ($mode) {
            case Keestash::MODE_WEB:
                $templatePath = $appRoot . "/template/app";
                $frontendPath = $templatePath . "/frontend";

                Keestash::getServer()
                    ->getTemplateManager()
                    ->addPath($templatePath);

                Keestash::getServer()
                    ->getFrontendTemplateManager()
                    ->addPath($frontendPath);

                break;
            case Keestash::MODE_API:
                $templatePath = $appRoot . "/template/email";

                Keestash::getServer()
                    ->getApiTemplateManager()
                    ->addPath($templatePath);

                break;
            default:
                throw new KeestashException();
        }


    }

    public static function getMode(): int {
        return self::$mode;
    }

    private static function initTemplates() {
        if (self::$mode === Keestash::MODE_API) return;

        $legacy = self::getServer()->getLegacy();

        /** @var IFileManager $fileManager */
        $fileManager = Keestash::getServer()->query(IFileManager::class);
        /** @var RawFileService $rawFileService */
        $rawFileService = Keestash::getServer()->query(RawFileService::class);
        /** @var FileService $fileService */
        $fileService = Keestash::getServer()->query(FileService::class);

        $file = $fileManager->read(
            $rawFileService->stringToUri(
                $fileService->getProfileImagePath(Keestash::getServer()->getUserFromSession())
            )
        );

        if (null === $file) {
            $file = $fileService->defaultProfileImage();
        }

        $userImage = $rawFileService->stringToBase64($file->getFullPath());
        self::$server->getTemplateManager()->replace("navigation.html",
            [
                "appName"     => $legacy->getVendor()->get("name")
                , "logopath"  => self::getBaseURL(false) . "/asset/img/logo_no_name.png"
                , "logoutURL" => self::getBaseURL() . "logout"
                , "userImage" => $userImage
            ]);

        self::$server->getTemplateManager()->replace("head.html",
            [
                "title"            => $legacy->getApplication()->get("name")
                , "stylecss"       => self::getBaseURL(false) . "/asset/css/style.css"
                , "faviconPath"    => self::getBaseURL(false) . "/asset/img/favicon.png"
                , "fontAwesomeCss" => "https://use.fontawesome.com/releases/v5.5.0/css/all.css"
                , "semanticUiCss"  => self::getBaseURL(false) . "lib/js/src/semantic/semantic.min.css"
                , "baseJs"         => self::getBaseURL(false) . "lib/js/dist/base.bundle.js"
                , "semanticJs"     => self::getBaseURL(false) . "lib/js/src/semantic/semantic.min.js"
            ]);

        Keestash::getServer()->getTemplateManager()->replace("no-content.html"
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
     * @return string
     */
    public static function getBaseURL(bool $withScript = true, bool $forceIndex = false): ?string {
        if (Keestash::MODE_NONE === Keestash::getMode()) return null;
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

        if ($withScript) {
            return substr($url, 0, strpos($url, $scriptName)) . $scriptNameToReplace;
        } else {
            return substr($url, 0, strpos($url, $scriptName)) . "";
        }
    }

    private static function isInstanceInstalled(): void {
        $lockHandler          = Keestash::getServer()->getInstanceLockHandler();
        $instanceDB           = Keestash::getServer()->getInstanceDB();
        $instanceHash         = $instanceDB->getOption(InstanceDB::FIELD_NAME_INSTANCE_HASH);
        $instanceId           = $instanceDB->getOption(InstanceDB::FIELD_NAME_INSTANCE_ID);
        $isLocked             = $lockHandler->isLocked();
        $routesToInstallation = RouterHelper::routesToInstallation();

        // TODO we need to route to install apps if the current
        //  route is going to another target
        if (true === $isLocked || true === $routesToInstallation) {
            return;
        }

        /** @var HTTPService $httpService */
        $httpService = Keestash::getServer()->query(HTTPService::class);

        if ((null === $instanceHash || null === $instanceId)) {
            FileLogger::debug("The whole application is not installed. Please install");
            $lockHandler->lock();
            $httpService->routeToInstallInstance();
            exit();
            die();
        }

    }

    private static function areAppsInstalled(): void {
        $instanceLockHandler = Keestash::getServer()->getInstanceLockHandler();

        // if we are actually installing the instance,
        // we need to make sure that Keestash does not want
        // to install the apps
        if (true === $instanceLockHandler->isLocked()) return;

        // TODO we need to route to install apps if the current
        //  route is going to another target
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = Keestash::getServer()->getAppLoader()->getApps();
        $installedApps = Keestash::getServer()->getAppRepository()->getAllApps();

        $diff          = new Diff();
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 1: we disable all apps that are disabled in our db
        $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we check if we have installed new apps
        if ($appsToInstall->size() > 0) {
            Keestash::handleNeedsUpgrade();
        }

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        if ($appsToUpgrade->size() > 0) {
            Keestash::handleNeedsUpgrade();
        }
        return;
    }

    private static function handleNeedsUpgrade(): void {
        /** @var LockHandler $lockHandler */
        $lockHandler = Keestash::getServer()->getAppLockHandler();
        $isLocked    = $lockHandler->isLocked();

        if (true === $isLocked) {
            FileLogger::debug("already locked. Do not test again!");
            return;
        }

        if (Keestash::getMode() === Keestash::MODE_WEB) {
            $lockHandler->lock();
            // in this case, we redirect to the install page
            // since the user is logged in and is in web mode
            Keestash::getServer()
                ->getHTTPRouter()
                ->routeTo("install");
            exit();
            die();
        }

        if (Keestash::getMode() === Keestash::MODE_API) {
            // in all other cases, we simply return an
            // "need to upgrade" JSON String
            self::getServer()->getResponseManager()->add(
                new NeedsUpgrade(
                    Keestash::getServer()->getL10N()
                )
            );
        }
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
        set_error_handler(function ($error) {

            if (is_int($error)) {
                FileLogger::error(json_encode($error));
                return;
            }

            if ($error instanceof Error) {
                FileLogger::error(json_encode($error->__toString()));
                return;
            }

            FileLogger::error((string) $error);

        });
        set_exception_handler(function ($exception) {
            if (is_int($exception)) {
                FileLogger::error(json_encode($exception));
                return;
            }

            if ($exception instanceof Exception) {
                FileLogger::error(json_encode($exception->__toString()));
                return;
            }

            FileLogger::error((string) $exception);
        });
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
        self::$server->getTemplateManager()->replace(
            "navigation.html"
            , [
                "href" => Helper::getDefaultURL()
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


    private static function addTopNavigation(): void {
        if (self::getMode() !== Keestash::MODE_WEB) return;
        $apps = self::$server->getAppLoader()->getApps();

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
        self::$server->getNavigationManager()->addPart(NavigationManager::NAVIGATION_TYPE_TOP, $part);
    }

    private static function renderTemplates() {
        Keestash::renderWebTemplates();
        Keestash::renderApiTemplates();
    }

    private static function renderApiTemplates() {
        if (self::$mode !== Keestash::MODE_API) return;
        Keestash::loadTemplates();
    }

    private static function renderWebTemplates() {
        if (Keestash::getMode() !== Keestash::MODE_WEB) return;
        Keestash::loadTemplates();
        Keestash::initTemplates();
        Keestash::addTopNavigation();

        $legacy = self::getServer()->getLegacy();

        if (!self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->isPublicRoute()) {
            self::$server->getTemplateManager()->replace("navigation.html",
                [
                    "baseURL"    => self::getBaseURL()
                    , "parts"    => self::$server
                    ->getNavigationManager()
                    ->getByName(NavigationManager::NAVIGATION_TYPE_TOP)
                    ->getAll()
                    , "settings" => self::getServer()
                    ->getNavigationManager()
                    ->getByName(NavigationManager::NAVIGATION_TYPE_SETTINGS)
                    ->getAll()
                ]
            );
        }

        $navigation = self::$server->getTemplateManager()->render("navigation.html");
        $appContent = self::$server->getTemplateManager()->render("app-content.html");

        $routeName = self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteName();

        self::$server->getTemplateManager()->replace("head.html",
            [
                "stylesheets"  => self::$server->getTemplateManager()->getStylesheets()
                , "scripts"    => self::$server->getTemplateManager()->getScripts()
                , "appScripts" => self::$server->getTemplateManager()->getAppScripts($routeName)
            ]);

        $head = self::$server->getTemplateManager()->render("head.html");
        if (!self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->isPublicRoute()) {
            self::$server->getTemplateManager()->replace(ITemplate::APP_NAVIGATION,
                [
                    "appNavigation"                 => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getAll()
                    , "hrefMain"                    => self::getBaseURL(true) . "/"
                    , "navigationActionText"        => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_URL)
                    , "navigationActionDetail"      => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_DETAIL)
                    , "navigationActionPlaceholder" => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_PLACEHOLDER)
                    , "deleteLeftMenuHeader"        => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_DELETE_MODAL_HEADER)
                    , "deleteLeftMenuContent"       => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_DELETE_MODAL_CONTENT)
                    , "deleteLeftMenuAnswerNo"      => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_DELETE_MODAL_ANSWER_NO)
                    , "deleteLeftMenuAnswerYes"     => self::$server->getNavigationManager()->getByName(NavigationManager::NAVIGATION_TYPE_APP)->getActionAtribute(Navigation::ACTION_ATTRIBUTE_DELETE_MODAL_ANSWER_YES)
                ]
            );
        }
        $appNavigation    = self::$server->getTemplateManager()->render(ITemplate::APP_NAVIGATION);
        $hasAppNavigation = (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER);
        $noContext        = (self::getServer()->getRouterManager()->get(IRouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER_CONTEXTLESS);
        $actionBar        = Keestash::renderActionBars(
            Keestash::getServer()->getActionBarManager()->get(IActionBarBag::ACTION_BAR_TOP)
        );

        Keestash::getServer()->getTemplateManager()->replace(
            ITemplate::BREADCRUMB
            , [
                "bc" => Keestash::getServer()->getBreadCrumbManager()->getAll()
            ]
        );

        $breadCrumb = Keestash::getServer()->getTemplateManager()->render(ITemplate::BREADCRUMB);

        self::$server->getTemplateManager()->replace(ITemplate::CONTENT,
            [
                "appNavigation"       => $appNavigation
                , "appContent"        => $appContent
                , "actionBar"         => $actionBar
                , "breadcrumbs"       => $breadCrumb
                , "appNavigationSize" => false === $hasAppNavigation ? "zero" : "two"
                , "appContentSize"    => false === $hasAppNavigation ? "sixteen" : "fourteen"
                , "hasAppNavigation"  => $hasAppNavigation
                , "hasActionBars"     => Keestash::getServer()->getActionBarManager()->isVisible()
                , "hasBreadcrumbs"    => Keestash::getServer()->getBreadCrumbManager()->isVisible()
            ]
        );

        $content = self::$server->getTemplateManager()->render(ITemplate::CONTENT);

        self::$server->getTemplateManager()->replace("footer.html",
            [
                "start_year"     => $legacy->getApplication()->get("start_date")->format("Y")
                , "current_year" => (new DateTime())->format("Y")
                , "appName"      => $legacy->getApplication()->get("name")
                , "vendor_name"  => $legacy->getVendor()->get("name")
                , "vendor_url"   => $legacy->getVendor()->get("web")
            ]
        );
        $footer = self::$server->getTemplateManager()->render("footer.html");

        self::$server->getTemplateManager()->replace(ITemplate::BODY,
            [
                "navigation"  => $navigation
                , "content"   => $content
                , "noContext" => $noContext
                , "footer"    => $footer
            ]
        );

        $body = self::$server->getTemplateManager()->render(ITemplate::BODY);

        $partTemplate       = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::PART_TEMPLATE);
        $sideBar            = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::SIDE_BAR);
        $breadCrumbTemplate = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::BREADCRUMB);

        self::$server->getTemplateManager()->replace(ITemplate::HTML,
            [
                "head"                 => $head
                , "host"               => Keestash::getBaseURL()
                , "apiHost"            => Keestash::getBaseAPIURL()
                , "body"               => $body
                , "noContext"          => $noContext
                , "partTemplate"       => $partTemplate
                , "sidebarTemplate"    => $sideBar
                , "breadcrumbTemplate" => $breadCrumbTemplate
            ]
        );
        $html = self::$server->getTemplateManager()->render(ITemplate::HTML);
        echo $html;
    }


    private static function renderActionBars(IActionBarBag $actionBarBag): string {
        $rendered = "";

        /** @var IActionBar $actionBar */
        foreach ($actionBarBag->getAll() as $actionBar) {
            if (false === $actionBar->hasElements()) continue;
            Keestash::getServer()->getTemplateManager()->replace(
                ITemplate::ACTION_BAR
                , [
                    "iconClass"  => $actionBar->getType()
                    , "elements" => $actionBar->getElements()
                    , "buttonId" => $actionBar->getName()
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


    private static function validateApi() {
        $responses = self::$server->getResponseManager()->getResponses();
        /** @var IResponse $response */
        $response = $responses->get(0);


        header('Access-Control-Allow-Origin: *');
        header("HTTP/1.1 {$response->getCode()} {$response->getDescription()}");

        foreach ($response->getHeaders() as $key => $header) {
            header("$key: $header");
        }
        echo $response->getMessage();

    }

}
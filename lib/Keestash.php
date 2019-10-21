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
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Log\Logger;
use Keestash\Api\Response\MaintenanceResponse;
use Keestash\Api\Response\NeedsUpgrade;
use Keestash\Api\Response\SessionExpired;
use Keestash\App\Config\Diff;
use Keestash\App\Helper;
use Keestash\Core\Manager\AssetManager\AssetManager;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use Keestash\Core\Manager\RouterManager\Route;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\Helper as RouterHelper;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Core\Manager\RouterManager\RouterManager;
use Keestash\Core\Manager\SessionManager\UserSessionManager;
use Keestash\Core\Service\HTTPService;
use Keestash\Core\Service\InstallerService;
use Keestash\Core\Service\MaintenanceService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\System\Installation\LockHandler;
use Keestash\Exception\NotInstalledException;
use Keestash\Server;
use Keestash\View\Navigation\Entry;
use Keestash\View\Navigation\Navigation;
use Keestash\View\Navigation\Part;
use KSP\Api\IResponse;
use KSP\App\IApp;
use KSP\Core\Manager\AssetManager\IAssetManager;
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
     * @throws DependencyException
     * @throws NotFoundException
     * @throws ReflectionException
     * @throws NotInstalledException
     */
    public static function requestWeb(): bool {
        Keestash::$mode = Keestash::MODE_WEB;
        Keestash::initRequest();

        /** @var UserSessionManager $sessionManager */
        $sessionManager = self::$server->query(UserSessionManager::class);
        /** @var HTTPRouter $router */
        $router = Keestash::getServer()->getHTTPRouter();

        if ($sessionManager->isUserLoggedIn() || $router->isPublicRoute()) {
            $router->route(null);
            $sessionManager->updateTimestamp();
        } else {
            $router->routeTo("login");
        }

        if (in_array($router->getRouteType(),
            [
                Route::ROUTE_TYPE_CONTROLLER
                , Route::ROUTE_TYPE_CONTROLLER_NO_APP_NAVIGATION
                , Route::ROUTE_TYPE_CONTROLLER_CONTEXTLESS
            ]
        )
        ) {
            Keestash::renderTemplates();
        }
        Keestash::flushOutput();
        return true;
    }

    private static function initRequest() {
        Keestash::init();

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
        Keestash::isInstalled();

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

        Keestash::loadTemplates();
        Keestash::initTemplates();
        return true;
    }

    private static function getAppRoot(): string {
        return str_replace("\\", '/', substr(__DIR__, 0, -3));
    }

    public static function getServer(): Server {
        return self::$server;
    }

    private static function loadTemplates() {
        $appRoot = self::getAppRoot();
        if (Keestash::getMode() === Keestash::MODE_WEB) {
            $templatePath = $appRoot . "/template/app";
        } else {
            if (Keestash::MODE_API === Keestash::getMode()) {
                $templatePath = $appRoot . "/template/email";
            } else {
                return;
            }
        }
        Keestash::getServer()->getTemplateManager()->addPath($templatePath);
    }

    public static function getMode(): int {
        return self::$mode;
    }

    private static function initTemplates() {
        if (self::$mode === Keestash::MODE_API) return;
        $legacy    = self::getServer()->getLegacy();
        $user      = self::getServer()->getUserFromSession();
        $userImage = "";

        if (null !== $user) {
            /** @var AssetManager $assetManager */
            $assetManager = self::getServer()->query(IAssetManager::class);
            $defaultImage = Keestash::getBaseURL(false) . "/asset/img/profile-picture.png";
            $profileImage = $assetManager->getProfilePicture($user);
            $image        = null !== $profileImage ? $profileImage : $defaultImage;
            $userImage    = $assetManager->uriToBase64($image);

        }

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

    private static function isInstalled(): void {
        $lockHandler          = Keestash::getServer()->getInstanceLockHandler();
        $isLocked             = $lockHandler->isLocked();
        $routesToInstallation = RouterHelper::routesToInstallation();

        // The app is locked and is possibly going to
        // install_instance route. Therefore, we can simply
        // return
        if (true === $isLocked && true === $routesToInstallation) {
            return;
        }

        Keestash::isInstanceInstalled();
        Keestash::areAppsInstalled();
    }

    private static function isInstanceInstalled(): void {
        /** @var HTTPService $httpService */
        $httpService = Keestash::getServer()->query(HTTPService::class);
        /** @var InstallerService $installer */
        $installer   = Keestash::getServer()->query(InstallerService::class);
        $isInstalled = $installer->isInstalled();

        if (false === $isInstalled) {
            FileLogger::debug("The whole application is not installed. Please install");
            $httpService->routeToInstallInstance();
            exit();
            die();
        }

    }

    private static function areAppsInstalled(): void {

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

        $lockHandler->lock();

        if (Keestash::getMode() === Keestash::MODE_WEB) {
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
        $router    = self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER);
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
        /** @var HashTable $config */
        $config = Keestash::getServer()->query(Server::CONFIG);
        if (false === $config->get("debug")) return;
        Keestash::installWhoops();
    }

    private static function installWhoops(): void {

        if (self::$mode === Keestash::MODE_API) return;
        $config     = self::getServer()->getConfig();
        $showErrors = $config->get("show_errors");
        if (true === $showErrors) {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();
        }
    }

    private static function initProductionHandler(): void {
        /** @var HashTable $config */
        $config = Keestash::getServer()->query(Server::CONFIG);
        if (true === $config->get("debug")) return;
        Keestash::hideErrors();
        Keestash::hideOutput();
    }

    private static function hideErrors() {
        $config     = self::getServer()->getConfig();
        $showErrors = $config->get("show_errors");
        $debug      = $config->get("debug");

        error_reporting(E_ALL | E_STRICT);
        @ini_set('display_errors', $showErrors && $debug ? '1' : '0');
        @ini_set('log_errors', $showErrors && $debug ? '1' : '0');
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
        /** @var HashTable $config */
        $config = Keestash::getServer()->query(Server::CONFIG);
        $debug  = $config->get("debug");

        if (true === $debug) return;
        if (null !== $callable) $callable(ob_get_contents());
        ob_end_clean();
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
        if (self::$mode === Keestash::MODE_API) return;
        Keestash::addTopNavigation();

        $legacy = self::getServer()->getLegacy();

        if (!self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER)->isPublicRoute()) {
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

        $routeName = self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER)->getRouteName();

        self::$server->getTemplateManager()->replace("head.html",
            [
                "stylesheets"  => self::$server->getTemplateManager()->getStylesheets()
                , "scripts"    => self::$server->getTemplateManager()->getScripts()
                , "appScripts" => self::$server->getTemplateManager()->getAppScripts($routeName)
            ]);

        $head = self::$server->getTemplateManager()->render("head.html");
        if (!self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER)->isPublicRoute()) {
            self::$server->getTemplateManager()->replace("app-navigation.html",
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
        $appNavigation    = self::$server->getTemplateManager()->render("app-navigation.html");
        $hasAppNavigation = (self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER);
        $noContext        = (self::getServer()->getRouterManager()->get(RouterManager::HTTP_ROUTER)->getRouteType() === Route::ROUTE_TYPE_CONTROLLER_CONTEXTLESS);
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

        self::$server->getTemplateManager()->replace(ITemplate::BODY_HTML,
            [
                "navigation"  => $navigation
                , "content"   => $content
                , "noContext" => $noContext
                , "footer"    => $footer
            ]
        );

        $body = self::$server->getTemplateManager()->render(ITemplate::BODY_HTML);

        $partTemplate       = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::PART_TEMPLATE);
        $sideBar            = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::SIDE_BAR);
        $breadCrumbTemplate = Keestash::getServer()->getTemplateManager()->getRawTemplate(ITemplate::BREADCRUMB);
        self::$server->getTemplateManager()->replace(ITemplate::BASE_FILE_NAME,
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
        $html = self::$server->getTemplateManager()->render(ITemplate::BASE_FILE_NAME);
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

    public static function requestApi(): void {
        Keestash::$mode = Keestash::MODE_API;
        Keestash::initRequest();
        /** @var APIRouter $router */
        $router    = Keestash::getServer()->getRouterManager()->get(RouterManager::API_ROUTER);
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
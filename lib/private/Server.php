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

namespace Keestash;

use Closure;
use Composer\Autoload\ClassLoader;
use DI\Container;
use DI\DependencyException;
use DI\NotFoundException;
use doganoo\Backgrounder\Backgrounder;
use doganoo\Backgrounder\Service\Log\ILoggerService;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\DI\String\IStringService;
use doganoo\DIP\String\StringService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\HTTP\Session;
use doganoo\SimpleRBAC\Handler\PermissionHandler;
use Keestash;
use Keestash\App\Loader;
use Keestash\Core\Backend\MySQLBackend;
use Keestash\Core\DTO\BackgroundJob\Logger;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Manager\ActionBarManager\ActionBarManager;
use Keestash\Core\Manager\BreadCrumbManager\BreadCrumbManager;
use Keestash\Core\Manager\ConsoleManager\ConsoleManager;
use Keestash\Core\Manager\CookieManager\CookieManager;
use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Manager\HookManager\ControllerHookManager;
use Keestash\Core\Manager\HookManager\PasswordChangedHookManager;
use Keestash\Core\Manager\HookManager\RegistrationHookManager;
use Keestash\Core\Manager\HookManager\ServiceHookManager;
use Keestash\Core\Manager\HookManager\SubmitHookManager;
use Keestash\Core\Manager\HookManager\User\UserRemovedHookManager;
use Keestash\Core\Manager\HookManager\User\UserStateHookManager;
use Keestash\Core\Manager\NavigationManager\App\NavigationManager as AppNavigationManager;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use Keestash\Core\Manager\ResponseManager\JSONResponseManager;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Core\Manager\RouterManager\Router\Router;
use Keestash\Core\Manager\RouterManager\RouterManager;
use Keestash\Core\Manager\SessionManager\SessionManager;
use Keestash\Core\Manager\StringManager\FrontendManager as FrontendStringManager;
use Keestash\Core\Manager\StylesheetManager\StylesheetManager;
use Keestash\Core\Manager\TemplateManager\ApiManager;
use Keestash\Core\Manager\TemplateManager\FrontendManager;
use Keestash\Core\Manager\TemplateManager\TwigManager;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\EncryptionKey\EncryptionKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Repository\Job\JobRepository;
use Keestash\Core\Repository\Permission\PermissionRepository;
use Keestash\Core\Repository\Permission\RoleRepository;
use Keestash\Core\Repository\Session\SessionRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Repository\User\UserStateRepository;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\DateTimeService;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\PublicFile\PublicFileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\HTTP\Input\SanitizerService as InputSanitizer;
use Keestash\Core\Service\HTTP\Output\SanitizerService as OutputSanitizer;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\Service\Log\LoggerService;
use Keestash\Core\Service\MaintenanceService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\ReflectionService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\Service\Stylesheet\Compiler;
use Keestash\Core\Service\TokenService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\App\LockHandler as AppLockHandler;
use Keestash\Core\System\Installation\Instance\LockHandler as InstanceLockHandler;
use Keestash\Core\System\System;
use Keestash\L10N\GetText;
use Keestash\Legacy\Legacy;
use Keestash\View\ActionBar\ActionBarBuilder;
use KSP\App\ILoader;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\ActionBarManager\IActionBarManager;
use KSP\Core\Manager\BreadCrumbManager\IBreadCrumbManager;
use KSP\Core\Manager\ConsoleManager\IConsoleManager;
use KSP\Core\Manager\CookieManager\ICookieManager;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\HookManager\IHookManager;
use KSP\Core\Manager\ResponseManager\IResponseManager;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\SessionManager\ISessionManager;
use KSP\Core\Manager\StylesheetManager\IStylesheetManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Permission\IDataProvider;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Job\IJobRepository;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\Session\ISessionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarBag;
use KSP\L10N\IL10N;
use SessionHandlerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use xobotyi\MimeType;

/**
 * Class Server
 *
 * @package Keestash
 */
class Server {

    public const ALLOWED_MIME_TYPES      = "types.mime.allowed";
    public const USER_HASH_MAP           = 'map.hash.user';
    public const USER_LIST               = 'list.user';
    public const CONFIG                  = 'config';
    public const DEFAULT_ACTION_BARS     = "bars.action.default";
    public const DEFAULT_ACTION_BAR_BAGS = "bags.bar.action.default";

    /** @var Container|null $container */
    private $container = null;
    /** @var string|null $appRoot */
    private $appRoot = null;
    /** @var ClassLoader|null $classLoader */
    private $classLoader = null;
    /** @var HashTable|null $userHashes */
    private $userHashes = null;
    /** @var ArrayList */
    private $userList = null;

    /**
     * Server constructor.
     *
     * @param string      $appRoot
     * @param ClassLoader $classLoader
     */
    public function __construct(
        string $appRoot
        , ClassLoader $classLoader
    ) {
        $this->appRoot     = $appRoot;
        $this->classLoader = $classLoader;
        $this->container   = new Container();

        $this->register(IL10N::class, function () {
            return new GetText();
        });

        $this->register(Compiler::class, function () {
            return new Compiler();
        });

        $this->register(InputSanitizer::class, function () {
            return new InputSanitizer();
        });

        $this->register(OutputSanitizer::class, function () {
            return new OutputSanitizer();
        });

        $this->register(Server::USER_HASH_MAP, function () {

            $this->userHashes = new HashTable();
            /** @var ArrayList $users */
            $users = $this->query(Server::USER_LIST);


            /** @var IUser $user */
            foreach ($users as $user) {
                $this->userHashes->put(
                    $user->getHash()
                    , $user->getId()
                );
            }
            return $this->userHashes;
        });

        $this->register(Server::USER_LIST, function () {
            /** @var InstallerService $installerService */
            $installerService = $this->query(InstallerService::class);

            if (false === $installerService->hasIdAndHash()) return null;
            if (null !== $this->userList) return $this->userList;

            $userRepository = $this->getUserRepository();
            $this->userList = $userRepository->getAll();
            return $this->userList;
        });

        $this->register(Verification::class, function () {
            return new Verification(
                Keestash::getServer()->getTokenManager()
                , Keestash::getServer()->getUserHashes()
            );
        });

        $this->register(IFileManager::class, function () {
            return new FileManager(
                $this->query(IFileRepository::class)
            );
        });

        $this->register(ControllerHookManager::class, function () {
            return new ControllerHookManager();
        });
        $this->register(SubmitHookManager::class, function () {
            return new SubmitHookManager();
        });
        $this->register(RegistrationHookManager::class, function () {
            return new RegistrationHookManager();
        });
        $this->register(PasswordChangedHookManager::class, function () {
            return new PasswordChangedHookManager();
        });
        $this->register(UserStateHookManager::class, function () {
            return new UserStateHookManager();
        });
        $this->register(UserRemovedHookManager::class, function () {
            return new UserRemovedHookManager();
        });

        $this->register(TokenService::class, function () {
            return new TokenService();
        });
        $this->register(IRoleRepository::class, function () {
            return new RoleRepository(
                $this->query(IBackend::class)
                , $this->query(IPermissionRepository::class)
            );
        });

        $this->register(Server::ALLOWED_MIME_TYPES, function () {
            $png = MimeType::getExtensionMimes(IExtension::PNG);
            $jpg = MimeType::getExtensionMimes(IExtension::JPG);
            $pdf = MimeType::getExtensionMimes(IExtension::PDF);

            return array_merge(
                $png
                , $jpg
                , $pdf
            );

        });
        $this->register(IRouterManager::class, function () {
            $routerManager     = new RouterManager();
            $loggerManager     = $this->query(IApiLogRepository::class);
            $reflectionService = $this->query(ReflectionService::class);

            $routerManager->add(
                IRouterManager::HTTP_ROUTER
                , new HTTPRouter(
                    $loggerManager
                    , $reflectionService
                )
            );

            $routerManager->add(
                IRouterManager::API_ROUTER
                , new APIRouter(
                    $loggerManager
                    , $reflectionService
                )
            );

            return $routerManager;
        });
        $this->register(IApiLogRepository::class, function () {
            return new ApiLogRepository(
                $this->query(IBackend::class)
            );
        });
        $this->register(ReflectionService::class, function () {
            return new ReflectionService();
        });
        $this->register(ILoader::class, function () {
            return new Loader(
                $this->classLoader
                , $this->appRoot
            );
        });
        $this->register(UserService::class, function () {
            return new UserService(
                $this->query(IApiLogRepository::class)
                , $this->query(IFileRepository::class)
                , $this->query(IEncryptionKeyRepository::class)
                , $this->query(IRoleRepository::class)
                , $this->query(IUserRepository::class)
                , $this->query(KeyService::class)
                , $this->query(Legacy::class)
                , $this->query(IUserStateRepository::class)
                , $this->query(FileService::class)
                , $this->query(InstanceRepository::class)
                , $this->query(CredentialService::class)
                , $this->query(IDateTimeService::class)
            );
        });
        
        $this->register(IBackend::class, function () {
            self::getSystem()->createConfig();
            return new MySQLBackend((string) self::getConfig()->get("db_name"));
        });

        $this->register(TwigManager::class, function () {
            $twigManager = new TwigManager();
            $twigManager->setUp(Keestash::getBaseURL(false));
            return $twigManager;
        });

        $this->register(ApiManager::class, function () {
            return new ApiManager();
        });

        $this->register(FrontendManager::class, function () {
            return new FrontendManager();
        });

        $this->register(ITemplateManager::class, function () {
            return $this->query(TwigManager::class);
        });

        $this->register(DateTimeService::class, function () {
            return new DateTimeService();
        });

        $this->register(RawFileService::class, function () {
            return new RawFileService();
        });

        $this->register(FileService::class, function () {
            return new FileService(
                $this->query(RawFileService::class)
            );
        });

        $this->register(InstanceDB::class, function () {
            return new InstanceDB();
        });

        $this->register(HTTPService::class, function () {
            return new HTTPService();
        });

        $this->register(ITokenRepository::class, function () {

            return new TokenRepository(
                $this->query(IBackend::class)
                , $this->query(IUserRepository::class)
            );
        });

        $this->register(IFileRepository::class, function () {
            return new FileRepository(
                $this->query(IBackend::class)
                , $this->query(IUserRepository::class)
            );
        });

        $this->register(ISessionManager::class, function () {
            return new SessionManager(
                $this->query(Session::class)
            );
        });

        $this->register(ICookieManager::class, function () {
            return new CookieManager();
        });

        $this->register(ISessionRepository::class, function () {
            return new SessionRepository(
                $this->query(IBackend::class)
            );
        });

        $this->register(SessionHandlerInterface::class, function () {
            return new Keestash\Core\Manager\SessionManager\SessionHandler(
                $this->query(ISessionRepository::class)
            );
        });

        $this->register(IResponseManager::class, function () {
            return new JSONResponseManager();
        });

        $this->register(Session::class, function () {
            return $this->query(SessionInterface::class);
        });

        $this->register(SessionInterface::class, function () {
            return new Session();
        });

        $this->register(System::class, function () {
            return new System();
        });

        $this->register(MaintenanceService::class, function () {
            return new MaintenanceService();
        });
        $this->register(Legacy::class, function () {
            return new Legacy();
        });

        $this->register(User::class, function () {
            return Keestash::getServer()->getUserFromSession();
        });

        $this->register(IUser::class, function () {
            return Keestash::getServer()->getUserFromSession();
        });

        $this->register(NavigationManager::class, function () {
            $navigationManager = new NavigationManager();
            $navigationManager->addNavigation(NavigationManager::NAVIGATION_TYPE_TOP);
            $navigationManager->addNavigation(NavigationManager::NAVIGATION_TYPE_APP);
            $navigationManager->addNavigation(NavigationManager::NAVIGATION_TYPE_SETTINGS);
            return $navigationManager;
        });

        $this->register(AppNavigationManager::class, function () {
            return new AppNavigationManager();
        });

        $this->register(IActionBarManager::class, function () {
            $actionBarManager = new ActionBarManager();
            $bags             = $this->query(Server::DEFAULT_ACTION_BAR_BAGS);

            foreach ($bags as $name => $bag) {
                $actionBarManager->add($name, $bag);
            }

            return $actionBarManager;
        });

        $this->register(Server::DEFAULT_ACTION_BAR_BAGS, function () {
            $actionBarBag = new View\ActionBar\Bag\ActionBarBag();
            $actionBars   = $this->query(Server::DEFAULT_ACTION_BARS);

            foreach ($actionBars as $name => $actionBar) {
                $actionBarBag->add($name, $actionBar);
            }
            return [
                IActionBarBag::ACTION_BAR_TOP => $actionBarBag
            ];
        });
        $this->register(Server::DEFAULT_ACTION_BARS, function () {
            $plus     = (new ActionBarBuilder(IActionBar::TYPE_PLUS))->build();
            $settings = (new ActionBarBuilder(IActionBar::TYPE_SETTINGS))->build();
            return [
                IActionBar::TYPE_PLUS       => $plus
                , IActionBar::TYPE_SETTINGS => $settings
            ];
        });


        $this->register(IUserRepository::class, function () {
            return new UserRepository(
                $this->query(IBackend::class)
                , $this->query(IRoleRepository::class)
                , $this->query(IDateTimeService::class)
            );
        });

        $this->register(IDateTimeService::class, function () {
            return new \doganoo\DIP\DateTime\DateTimeService();
        });

        $this->register(IPermissionRepository::class, function () {
            return new PermissionRepository(
                $this->query(IBackend::class)
            );
        });

        $this->register(InstallerService::class, function () {
            return new InstallerService(
                $this->getInstanceLockHandler()
                , $this->query(Migrator::class)
                , $this->query(InstanceDB::class)
                , $this->query(ConfigService::class)
            );
        });

        $this->register(LoggerService::class, function () {
            return new LoggerService();
        });

        $this->register(Migrator::class, function () {
            return new Migrator();
        });

        $this->register(IConsoleManager::class, function () {
            return new ConsoleManager();
        });

        $this->register(IEncryptionKeyRepository::class, function () {
            return new EncryptionKeyRepository(
                $this->query(IBackend::class)
            );
        });

        $this->register(IDataProvider::class, function () {
            return new Keestash\Core\Permission\DataProvider(
                $this->query(User::class)
                , $this->query(IPermissionRepository::class)
            );
        });
        $this->register(PermissionHandler::class, function () {
            return new PermissionHandler(
                $this->query(IDataProvider::class
                )
            );
        });

        $this->register(IAppRepository::class, function () {
            return new AppRepository(
                Keestash::getServer()->query(IBackend::class)
            );
        });

        $this->register(AppLockHandler::class, function () {
            return new AppLockHandler(
                $this->query(InstanceDB::class)
            );
        });

        $this->register(InstanceLockHandler::class, function () {
            return new InstanceLockHandler(
                $this->query(InstanceDB::class)
            );
        });

        $this->register(IBreadCrumbManager::class, function () {
            return new BreadCrumbManager();
        });

        $this->register(ConfigService::class, function () {
            return new ConfigService(
                $this->query(Server::CONFIG)
            );
        });

        $this->register(PersistenceService::class, function () {
            return new PersistenceService(
                $this->query(ISessionManager::class)
                , $this->query(ICookieManager::class)
            );
        });

        $this->register(PublicFileService::class, function () {
            return new PublicFileService();
        });

        $this->register(FrontendStringManager::class, function () {
            return new FrontendStringManager();
        });

        $this->register(Backgrounder::class, function () {

            return new Backgrounder(
                Keestash::getServer()->getJobRepository()->getJobList()
                , Keestash::getServer()->query(Core\DTO\BackgroundJob\Container::class)
                , Keestash::getServer()->query(ILoggerService::class)
            );

        });

        $this->register(ILoggerService::class, function () {
            return new Logger();
        });

        $this->register(IEncryptionService::class, function () {
            return new KeestashEncryptionService();
        });

        $this->register(CredentialService::class, function () {
            return new CredentialService();
        });

        $this->register(IStringService::class, function () {
            return new StringService();
        });

        $this->register(Core\DTO\BackgroundJob\Container::class, function () {
            return new Core\DTO\BackgroundJob\Container();
        });

        $this->register(IJobRepository::class, function () {
            return new JobRepository(
                Keestash::getServer()->query(IBackend::class)
            );
        });

        $this->register(IUserStateRepository::class, function () {
            return new UserStateRepository(
                Keestash::getServer()->query(IBackend::class)
                , Keestash::getServer()->getUserRepository()
            );
        });

        $this->register(InstanceRepository::class, function () {
            return new InstanceRepository(
                Keestash::getServer()->query(IBackend::class)
            );
        });

        $this->register(IStylesheetManager::class, function () {
            return new StylesheetManager();
        });

    }

    public function register(string $name, Closure $closure): bool {
        $this->container->set($name, $closure);
        return true;
    }

    /**
     * @param string $name
     *
     * @return mixed
     * @throws DependencyException
     * @throws NotFoundException
     */
    public function query(string $name) {
        if ($this->container->has($name)) {
            return $this->container->get($name);
        }
        return null;
    }

    public function getUserRepository(): IUserRepository {
        return $this->query(IUserRepository::class);
    }

    public function getTokenManager(): ITokenRepository {
        return $this->query(ITokenRepository::class);
    }

    public function getUserHashes(): ?HashTable {
        return $this->query(Server::USER_HASH_MAP);
    }

    public function getSystem(): System {
        return $this->query(System::class);
    }

    public function getConfig(): HashTable {
        return $this->query(Server::CONFIG);
    }

    public function getUserFromSession(): ?IUser {
        /** @var PersistenceService $persistenceService */
        $persistenceService = $this->query(PersistenceService::class);
        /** @var UserService $userService */
        $userService = $this->query(UserService::class);
        $userId      = $persistenceService->getValue("user_id", null);


        if (null === $userId) return null;

        /** @var IUserRepository $userManager */
        $userManager = $this->query(IUserRepository::class);
        $user        = $userManager->getUserById($userId);

        if (true === $userService->isDisabled($user)) return null;
        return $user;
    }

    public function getInstanceLockHandler(): InstanceLockHandler {
        return $this->query(InstanceLockHandler::class);
    }

    public function getJobRepository(): IJobRepository {
        return $this->query(IJobRepository::class);
    }

    public function getUsersFromCache(): ArrayList {
        return $this->query(Server::USER_LIST);
    }

    public function getImageRoot(): string {
        return $this->getDataRoot() . "image/";
    }

    public function getDataRoot(): string {
        return $this->appRoot . "data/";
    }

    public function getSCSSRoot(): string {
        return $this->getServerRoot() . "lib/scss/";
    }

    public function getServerRoot(): string {
        return $this->appRoot;
    }

    public function getAppRoot(): string {
        return $this->appRoot . "/apps/";
    }

    public function getAssetRoot(): string {
        return $this->getServerRoot() . "asset/";
    }

    public function getPhinxRoot(): string {
        return $this->getConfigRoot() . "phinx/";
    }

    public function getConfigRoot(): string {
        return $this->appRoot . "config/";
    }

    public function getLogfilePath(): string {
        $name    = $this->getLegacy()->getApplication()->get("name_internal");
        $logFile = $this->getDataRoot() . "$name.log";
        return $logFile;
    }

    public function getLegacy(): Legacy {
        return new Legacy();
    }

    public function getConfigfilePath(): string {
        return $this->getConfigRoot() . "config.php";
    }

    public function getTemplateManager(): TwigManager {
        return $this->query(TwigManager::class);
    }

    public function getApiTemplateManager(): ApiManager {
        return $this->query(ApiManager::class);
    }

    public function getFrontendTemplateManager(): FrontendManager {
        return $this->query(FrontendManager::class);
    }

    public function getFrontendStringManager(): FrontendStringManager {
        return $this->query(FrontendStringManager::class);
    }

    public function getActionBarManager(): IActionBarManager {
        return $this->query(IActionBarManager::class);
    }

    public function getNavigationManager(): NavigationManager {
        return $this->query(NavigationManager::class);
    }

    public function getConsoleManager(): IConsoleManager {
        return $this->query(IConsoleManager::class);
    }

    public function getRouter(): Router {

        switch (Keestash::getMode()) {
            case Keestash::MODE_WEB:
                return $this->getHTTPRouter();
                break;
            case Keestash::MODE_API:
                return $this->getApiRouter();
            default:
                return $this->getHTTPRouter();
        }
    }

    public function getHTTPRouter(): HTTPRouter {
        /** @var HTTPRouter $router */
        $router = $this->getRouterManager()->get(IRouterManager::HTTP_ROUTER);
        return $router;
    }

    public function getRouterManager(): RouterManager {
        return $this->query(IRouterManager::class);
    }

    public function getApiRouter(): APIRouter {
        /** @var APIRouter $router */
        $router = $this->getRouterManager()->get(IRouterManager::API_ROUTER);
        return $router;
    }

    public function getL10N(): IL10N {
        return $this->query(IL10N::class);
    }

    public function getControllerHookManager(): IHookManager {
        return $this->query(ControllerHookManager::class);
    }

    public function getSubmitHookManager(): IHookManager {
        return $this->query(SubmitHookManager::class);
    }

    public function getRegistrationHookManager(): IHookManager {
        return $this->query(RegistrationHookManager::class);
    }

    public function getUserStateHookManager(): IHookManager {
        return $this->query(UserStateHookManager::class);
    }

    public function getUserRemovedHookManager(): IHookManager {
        return $this->query(UserRemovedHookManager::class);
    }

    public function getPasswordChangedHookManager(): IHookManager {
        return $this->query(PasswordChangedHookManager::class);
    }

    public function getServiceHookManager(): IHookManager {
        return $this->query(ServiceHookManager::class);
    }

    public function getResponseManager(): IResponseManager {
        return $this->query(IResponseManager::class);
    }

    public function getAppLoader(): ILoader {
        return $this->query(ILoader::class);
    }

    public function getInstanceDB(): InstanceDB {
        return $this->query(InstanceDB::class);
    }

    public function getPermissionHandler(): PermissionHandler {
        return $this->query(PermissionHandler::class);
    }

    public function getAppRepository(): IAppRepository {
        return $this->query(IAppRepository::class);
    }

    public function getAppLockHandler(): AppLockHandler {
        return $this->query(AppLockHandler::class);
    }

    public function getBreadCrumbManager(): IBreadCrumbManager {
        return $this->query(IBreadCrumbManager::class);
    }

    public function getBackgrounder(): Backgrounder {
        return $this->query(Backgrounder::class);
    }

    public function getStylesheetManager(): IStylesheetManager {
        return $this->query(IStylesheetManager::class);
    }

    public function wipeCache(): void {
        $this->userHashes = null;
        $this->userList   = null;
    }

}

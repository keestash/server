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
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Handler\PermissionHandler;
use Keestash;
use Keestash\App\Loader;
use Keestash\Core\Backend\MySQLBackend;
use Keestash\Core\DTO\User;
use Keestash\Core\Encryption\Base\BaseEncryption;
use Keestash\Core\Encryption\Base\Credential;
use Keestash\Core\Manager\ActionBarManager\ActionBarManager;
use Keestash\Core\Manager\AssetManager\AssetManager;
use Keestash\Core\Manager\BreadCrumbManager\BreadCrumbManager;
use Keestash\Core\Manager\HookManager\ControllerHookManager;
use Keestash\Core\Manager\HookManager\PasswordChangedHookManager;
use Keestash\Core\Manager\HookManager\RegistrationHookManager;
use Keestash\Core\Manager\HookManager\ServiceHookManager;
use Keestash\Core\Manager\HookManager\SubmitHookManager;
use Keestash\Core\Manager\NavigationManager\NavigationManager;
use Keestash\Core\Manager\ResponseManager\JSONResponseManager;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Core\Manager\RouterManager\Router\Router;
use Keestash\Core\Manager\RouterManager\RouterManager;
use Keestash\Core\Manager\SessionManager\SessionManager;
use Keestash\Core\Manager\SessionManager\UserSessionManager;
use Keestash\Core\Manager\TemplateManager\TwigManager;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use Keestash\Core\Repository\AppRepository\AppRepository;
use Keestash\Core\Repository\EncryptionKey\EncryptionKeyRepository;
use Keestash\Core\Repository\File\FileRepository;
use Keestash\Core\Repository\Permission\PermissionRepository;
use Keestash\Core\Repository\Permission\RoleRepository;
use Keestash\Core\Repository\Token\TokenRepository;
use Keestash\Core\Repository\User\UserRepository;
use Keestash\Core\Service\DateTimeService;
use Keestash\Core\Service\HTTPService;
use Keestash\Core\Service\InstallerService;
use Keestash\Core\Service\MaintenanceService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\ReflectionService;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\Service\TokenService;
use Keestash\Core\Service\UserService;
use Keestash\Core\System\Installation\App\LockHandler as AppLockHandler;
use Keestash\Core\System\Installation\Instance\HealthCheck;
use Keestash\Core\System\Installation\Instance\LockHandler as InstanceLockHandler;
use Keestash\Core\System\System;
use Keestash\L10N\GetText;
use Keestash\Legacy\Legacy;
use Keestash\View\ActionBar\ActionBarBuilder;
use KSP\App\ILoader;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\ActionBarManager\IActionBarManager;
use KSP\Core\Manager\AssetManager\IAssetManager;
use KSP\Core\Manager\BreadCrumbManager\IBreadCrumbManager;
use KSP\Core\Manager\HookManager\IHookManager;
use KSP\Core\Manager\ResponseManager\IResponseManager;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\SessionManager\ISessionManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Permission\IDataProvider;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarBag;
use KSP\L10N\IL10N;
use xobotyi\MimeType;

class Server {

    public const ALLOWED_IMAGE_MIME_TYPES = "types.mime.image.allowed";
    public const USER_HASH_MAP            = 'map.hash.user';
    public const CONFIG                   = 'config';
    const        DEFAULT_ACTION_BARS      = "defaultActionBars";
    const        DEFAULT_ACTION_BAR_BAGS  = "defaultActionBarBAgs";

    /** @var Container|null $container */
    private $container = null;
    /** @var string|null $appRoot */
    private $appRoot = null;
    /** @var ClassLoader|null $classLoader */
    private $classLoader = null;
    /** @var HashTable|null $userHashes */
    private $userHashes = null;

    /**
     * Server constructor.
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

        $this->register(Server::USER_HASH_MAP, function () {
            $healthCheck = new HealthCheck();
            if (false === $healthCheck->readInstallation()) return null;
            if (null !== $this->userHashes) return $this->userHashes;

            $this->userHashes = new HashTable();
            /** @var IUserRepository $userManager */
            $userManager = $this->getUserRepository();
            /** @var UserService $userService */
            $userService = $this->query(UserService::class);

            $users = $userManager->getAll();

            if (null === $users) return $this->userHashes;

            /** @var IUser $user */
            foreach ($users as $user) {
                $this->userHashes->put(
                    $user->getHash()
                    , $user->getId()
                );
            }
            return $this->userHashes;
        });

        $this->register(Verification::class, function () {
            return new Verification(
                Keestash::getServer()->getTokenManager()
                , Keestash::getServer()->getUserHashes()
            );
        });

        $this->register(ControllerHookManager::class, function () {
            return new ControllerHookManager(null);
        });
        $this->register(SubmitHookManager::class, function () {
            return new SubmitHookManager(null);
        });
        $this->register(RegistrationHookManager::class, function () {
            return new RegistrationHookManager(null);
        });
        $this->register(PasswordChangedHookManager::class, function () {
            return new PasswordChangedHookManager(null);
        });
        $this->register(IAssetManager::class, function () {
            return new AssetManager(null, null);
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

        $this->register(Server::ALLOWED_IMAGE_MIME_TYPES, function () {
            $png = MimeType::getExtensionMimes("png");
            $jpg = MimeType::getExtensionMimes("jpg");

            return array_merge(
                $png
                , $jpg
            );
        });
        $this->register(IRouterManager::class, function () {
            $routerManager     = new RouterManager(null, null);
            $loggerManager     = $this->query(IApiLogRepository::class);
            $reflectionService = $this->query(ReflectionService::class);

            $routerManager->add(
                RouterManager::HTTP_ROUTER
                , new HTTPRouter(
                    $loggerManager
                    , $reflectionService
                )
            );

            $routerManager->add(
                RouterManager::API_ROUTER
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
        $this->register(IBackend::class, function () {
            self::getSystem()->createConfig();
            return new MySQLBackend((string) self::getConfig()->get("db_name"));
        });

        $this->register(ITemplateManager::class, function () {
            $twigManager = new TwigManager();
            $twigManager->setUp(Keestash::getBaseURL(false));
            return $twigManager;
        });

        $this->register(DateTimeService::class, function () {
            return new DateTimeService();
        });

        $this->register("test", function () {

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

        $this->register(UserSessionManager::class, function () {
            $backend = $this->query(IBackend::class);
            return new UserSessionManager($backend, null);
        });

        $this->register(ISessionManager::class, function () {
            $backend = $this->query(IBackend::class);
            return new SessionManager($backend, null);
        });

        $this->register(IResponseManager::class, function () {
            $backend = $this->query(IBackend::class);
            return new JSONResponseManager($backend, null);
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

        $this->register(IActionBarManager::class, function () {
            $actionBarManager = new ActionBarManager();
            $bags             = $this->query(Server::DEFAULT_ACTION_BAR_BAGS);

            foreach ($bags as $name => $bag) {
                $actionBarManager->add($name, $bag);
            }

            return $actionBarManager;
        });

        $this->register(Server::DEFAULT_ACTION_BAR_BAGS, function () {
            $actionBarBag = new Keestash\View\ActionBar\ActionBarBag();
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
            );
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
            );
        });

        $this->register(Migrator::class, function () {
            return new Migrator();
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
            return new AppLockHandler();
        });

        $this->register(InstanceLockHandler::class, function () {
            return new InstanceLockHandler();
        });

        $this->register(IBreadCrumbManager::class, function () {
            return new BreadCrumbManager();
        });

        $this->register(HealthCheck::class, function () {
            return new HealthCheck();
        });

    }

    public function register(string $name, Closure $closure): bool {
        $this->container->set($name, $closure);
        return true;
    }

    public function getUserRepository(): IUserRepository {
        return $this->query(IUserRepository::class);
    }

    /**
     * @param string $name
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
        return $this->query("config");
    }

    public function getUserFromSession(): ?IUser {
        /** @var UserSessionManager $sessionManager */
        $sessionManager = $this->query(UserSessionManager::class);
        /** @var IUserRepository $userManager */
        $userManager = $this->query(IUserRepository::class);
        $userId      = $sessionManager->getUser();
        if (null === $userId) return null;
        return $userManager->getUserById($userId);
    }

    public function getImageRoot(): string {
        return $this->getDataRoot() . "image/";
    }

    public function getDataRoot(): string {
        return $this->appRoot . "data/";
    }

    public function getLockRoot(): string {
        return $this->appRoot . "data/lock/";
    }

    public function getInstallerRoot(): string {
        return $this->getLockRoot() . "installer/";
    }

    public function getPhinxRoot(): string {
        return $this->getConfigRoot() . "phinx/";
    }

    public function getLogfilePath(): string {
        $name    = $this->getLegacy()->getApplication()->get("name_internal");
        $logFile = $this->getDataRoot() . "$name.log";
        return $logFile;
    }

    public function getConfigfilePath(): string {
        $logFile = $this->getConfigRoot() . "config.php";
        return $logFile;
    }

    public function getBaseEncryption(?IUser $user = null) {
        // this breaks the DI principle.
        // However, i do not know how to make this better
        if (null === $user) {
            $user = $this->getUserFromSession();
        }
        $credential = new Credential($user);
        return new BaseEncryption($credential);

    }

    public function getServerRoot(): string {
        return $this->appRoot;
    }

    public function getAppRoot(): string {
        return $this->appRoot . "/apps/";
    }

    public function getTemplateManager(): ITemplateManager {
        return $this->query(ITemplateManager::class);
    }

    public function getActionBarManager(): IActionBarManager {
        return $this->query(IActionBarManager::class);
    }


    public function getLegacy(): Legacy {
        return new Legacy();
    }

    public function getConfigRoot(): string {
        return $this->appRoot . "config/";
    }

    public function getNavigationManager(): NavigationManager {
        return $this->query(NavigationManager::class);
    }

    public function getRouterManager(): RouterManager {
        return $this->query(IRouterManager::class);
    }

    public function getHTTPRouter(): HTTPRouter {
        return $this->getRouterManager()->get(RouterManager::HTTP_ROUTER);
    }

    public function getApiRouter(): APIRouter {
        return $this->getRouterManager()->get(RouterManager::API_ROUTER);
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

    public function getPermissionHandler(): PermissionHandler {
        return $this->query(PermissionHandler::class);
    }

    public function getAppRepository(): IAppRepository {
        return $this->query(IAppRepository::class);
    }

    public function getAppLockHandler(): AppLockHandler {
        return $this->query(AppLockHandler::class);
    }

    public function getInstanceLockHandler(): InstanceLockHandler {
        return $this->query(InstanceLockHandler::class);
    }

    public function getBreadCrumbManager(): IBreadCrumbManager {
        return $this->query(IBreadCrumbManager::class);
    }

}
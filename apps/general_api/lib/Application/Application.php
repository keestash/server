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

namespace KSA\GeneralApi\Application;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\Stylesheet\Compiler as StylesheetCompiler;
use KSA\general_api\lib\Api\UserList;
use KSA\GeneralApi\Api\MinimumCredential;
use KSA\GeneralApi\Api\Organization\Activate;
use KSA\GeneralApi\Api\Organization\Add;
use KSA\GeneralApi\Api\Organization\Get;
use KSA\GeneralApi\Api\Organization\ListAll;
use KSA\GeneralApi\Api\Organization\Update;
use KSA\GeneralApi\Api\Organization\User;
use KSA\GeneralApi\Api\Template\GetAll;
use KSA\GeneralApi\Api\Thumbnail\File;
use KSA\GeneralApi\Command\Migration\MigrateApps;
use KSA\GeneralApi\Command\QualityTool\ClearBundleJS;
use KSA\GeneralApi\Command\QualityTool\PHPStan;
use KSA\GeneralApi\Command\Stylesheet\Compiler;
use KSA\GeneralApi\Controller\Organization\Detail;
use KSA\GeneralApi\Controller\Route\RouteList;
use KSA\GeneralApi\Event\Listener\UserChangedListener;
use KSA\GeneralApi\Event\Organization\UserChangedEvent;
use KSA\GeneralApi\Repository\IOrganizationRepository;
use KSA\GeneralApi\Repository\IOrganizationUserRepository;
use KSA\GeneralApi\Repository\OrganizationRepository;
use KSA\GeneralApi\Repository\OrganizationUserRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\IEncryptionService;

/**
 * Class Application
 *
 * @package Keestash\Api\Core
 */
class Application extends Keestash\App\Application {

    public const APP_ID = 'general_api';

    public const PASSWORD_REQUIREMENTS    = "password_requirements/";
    public const ALL_USERS                = "users/all/{type}/";
    public const FILE_ICONS               = "icon/file/get/{extension}/";
    public const FRONTEND_TEMPLATES       = "frontend_templates/all/";
    public const FRONTEND_STRINGS         = "frontend_strings/all/";
    public const ROUTE_LIST               = "route_list/all/";
    public const ORGANIZATION_LIST        = "organizations/all/";
    public const ORGANIZATION_ADD         = "organizations/add/";
    public const ORGANIZATION_ACTIVATE    = "organizations/activate/";
    public const ORGANIZATION_SINGLE      = "organizations/{id}/";
    public const ORGANIZATION_UPDATE      = "organizations/update/";
    public const ORGANIZATION_USER_CHANGE = "organizations/user/change/";

    public function register(): void {
        $this->registerServices();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerJavascript();
        $this->registerApiRoutes();
    }

    private function registerApiRoutes(): void {
        $this->registerApiRoute(
            Application::ORGANIZATION_USER_CHANGE
            , User::class
            , [IRouterManager::POST]
        );
        $this->registerApiRoute(
            Application::ORGANIZATION_UPDATE
            , Update::class
            , [IRouterManager::POST]
        );
        $this->registerApiRoute(
            Application::PASSWORD_REQUIREMENTS
            , MinimumCredential::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::ORGANIZATION_LIST
            , ListAll::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::ORGANIZATION_ADD
            , Add::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::ORGANIZATION_ACTIVATE
            , Activate::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::ALL_USERS
            , UserList::class
            , [IRouterManager::GET]
        );
        $this->registerApiRoute(
            Application::FRONTEND_TEMPLATES
            , GetAll::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::FRONTEND_STRINGS
            , \KSA\GeneralApi\Api\Strings\GetAll::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::FILE_ICONS
            , File::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::ORGANIZATION_SINGLE
            , Get::class
            , [IRouterManager::GET]
        );

        $this->registerPublicApiRoute(
            Application::PASSWORD_REQUIREMENTS
        );
        $this->registerPublicApiRoute(
            Application::FRONTEND_STRINGS
        );
        $this->registerPublicApiRoute(
            Application::FRONTEND_TEMPLATES
        );
        $this->registerPublicApiRoute(
            Application::FILE_ICONS
        );

        $this->registerListener();
    }

    private function registerListener(): void {
        Keestash::getServer()
            ->getEventManager()
            ->registerListener(
                UserChangedEvent::class
                , new UserChangedListener(
                    Keestash::getServer()->query(IOrganizationUserRepository::class)
                    , Keestash::getServer()->query(IOrganizationKeyRepository::class)
                    , Keestash::getServer()->query(IEncryptionService::class)
                    , Keestash::getServer()->query(CredentialService::class)
                    , Keestash::getServer()->query(ILogger::class)
                )
            );
    }

    private function registerServices(): void {
        Keestash::getServer()
            ->register(IOrganizationRepository::class, function () {
                return new OrganizationRepository(
                    Keestash::getServer()->query(IOrganizationUserRepository::class)
                    , Keestash::getServer()->query(IDateTimeService::class)
                    , Keestash::getServer()->query(IBackend::class)
                );
            });
        Keestash::getServer()
            ->register(IOrganizationUserRepository::class, function () {
                return new OrganizationUserRepository(
                    Keestash::getServer()->query(IUserRepository::class)
                    , Keestash::getServer()->query(IBackend::class)
                    , Keestash::getServer()->query(ILogger::class)
                    , Keestash::getServer()->query(IDateTimeService::class)
                );
            });
    }

    private function registerCommands(): void {
        $this->registerCommand(
            new MigrateApps(
                Keestash::getServer()->query(Migrator::class)
            )
        );
        $this->registerCommand(
            new PHPStan(
                Keestash::getServer()->getServerRoot()
            )
        );

        $this->registerCommand(
            new ClearBundleJS(
                Keestash::getServer()->getServerRoot()
                , Keestash::getServer()->getAppRoot()
            )
        );

        $this->registerCommand(
            new Compiler(
                Keestash::getServer()->query(StylesheetCompiler::class)
                , Keestash::getServer()->getSCSSRoot()
            )
        );

    }

    private function registerJavascript(): void {
        $this->addJavaScriptFor(
            Application::APP_ID
            , "organization_detail"
            , Application::ORGANIZATION_SINGLE
        );
    }

    private function registerRoutes(): void {
        $this->registerRoute(
            Application::ROUTE_LIST
            , RouteList::class
        );

        $this->registerRoute(
            Application::ORGANIZATION_SINGLE
            , Detail::class
        );
    }

}

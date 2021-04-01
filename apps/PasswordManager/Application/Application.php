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

namespace KSA\PasswordManager\Application;

use Keestash;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\Service\Validation\ValidationService;
use Keestash\Legacy\Legacy;
use KSA\PasswordManager\Api\Comment\Add;
use KSA\PasswordManager\Api\Comment\Remove;
use KSA\PasswordManager\Api\Generate\Generate;
use KSA\PasswordManager\Api\Import\Import;
use KSA\PasswordManager\Api\Node\Avatar\Update;
use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\Folder\Create;
use KSA\PasswordManager\Api\Node\Get;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\Organization;
use KSA\PasswordManager\Api\Node\ShareableUsers;
use KSA\PasswordManager\Api\Share\PublicShare;
use KSA\PasswordManager\Api\Share\PublicShareSingle;
use KSA\PasswordManager\Api\Share\Share;
use KSA\PasswordManager\Command\Node\Credential\CreateCredential;
use KSA\PasswordManager\Command\Node\Folder\CreateFolder;
use KSA\PasswordManager\Controller\Attachment\View;
use KSA\PasswordManager\Controller\PasswordManager\Controller;
use KSA\PasswordManager\Controller\PublicShare\PublicShareController;
use KSA\PasswordManager\Event\Listener\AfterPasswordChanged;
use KSA\PasswordManager\Event\Listener\AfterRegistration;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateKey;
use KSA\PasswordManager\Event\Listener\AfterRegistration\CreateStarterPassword;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService as NodeCredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\L10N\IL10N;

class Application extends Keestash\App\Application {

    public const APP_ID                                = "password_manager";
    public const PERMISSION_PWM                        = "password_manager";
    public const PERMISSION_PWM_CREDENTIAL_GET         = "password_manager_credential_get";
    public const PERMISSION_PWM_FOLDER_CREATE          = "password_manager_folder_create";
    public const PERMISSION_PWM_NODE_CREDENTIAL_CREATE = "password_manager_password_credential_create";

    public const PASSWORD_MANAGER                        = "password_manager";
    public const PASSWORD_MANAGER_NODE_GET               = "password_manager/node/get/{id}/";
    public const PASSWORD_MANAGER_NODE_GET_BY_NAME       = "password_manager/node/name/{name}/";
    public const PASSWORD_MANAGER_NODE_CREDENTIAL_CREATE = "password_manager/node/credential/create/";
    public const PASSWORD_MANAGER_NODE_CREATE            = "password_manager/node/create/";
    public const PASSWORD_MANAGER_NODE_DELETE            = "password_manager/node/delete/";
    public const PASSWORD_MANAGER_NODE_MOVE              = "password_manager/node/move/";

    public const PASSWORD_MANAGER_NODE_UPDATE_AVATAR = "password_manager/node/update/avatar/";
    public const PASSWORD_MANAGER_NODE_GET_AVATAR    = "password_manager/node/get/avatar/{nodeId}/";

    public const PASSWORD_MANAGER_CREDENTIAL_GET = "/password_manager/credential/get/{id}/";

    public const PASSWORD_MANAGER_IMPORT = "/password_manager/import/";

    public const PASSWORD_MANAGER_SHARE                = "/password_manager/share/";
    public const PASSWORD_MANAGER_SHARE_REMOVE         = "/password_manager/share/remove/";
    public const PASSWORD_MANAGER_SHARE_PUBLIC         = "/password_manager/share/public/";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE  = "/s/{hash}/";
    public const PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT = "/password_manager/public_share/decrypt/{hash}/";

    public const PASSWORD_MANAGER_SHAREABLE_USERS = "/password_manager/users/shareable/{nodeId}/";
    public const PASSWORD_MANAGER_USERS_UPDATE    = "/password_manager/users/update/";

    public const PASSWORD_MANAGER_GENERATE_PASSWORD = "/password_manager/generate_password/{length}/{upperCase}/{lowerCase}/{digit}/{specialChars}/";

    public const PASSWORD_MANAGER_ADD_COMMENT    = "/password_manager/comment/add/";
    public const PASSWORD_MANAGER_REMOVE_COMMENT = "/password_manager/comment/remove/";
    public const PASSWORD_MANAGER_GET_COMMENT    = "/password_manager/comment/get/{nodeId}/";

    public const PASSWORD_MANAGER_ATTACHMENTS_ADD    = "/password_manager/attachments/add/{token}/{user_hash}/";
    public const PASSWORD_MANAGER_ATTACHMENTS_GET    = "/password_manager/attachments/get/{nodeId}/";
    public const PASSWORD_MANAGER_ATTACHMENTS_VIEW   = "/password_manager/attachments/view/{fileId}/";
    public const PASSWORD_MANAGER_ATTACHMENTS_REMOVE = "/password_manager/attachments/remove/";

    public const PASSWORD_MANAGER_ORGANIZATION_ADD_NODE = "/password_manager/organization/node/add/";

    public const BREADCRUMB = "/breadcrumbs/get/{id}/";

    public const ROOT_FOLDER = "root";

    public const DEFAULT_PASSWORD_LENGTH     = 0;
    public const MINIMUM_PASSWORD_CHARACTERS = 0;
    public const MAXIMUM_PASSWORD_CHARACTERS = 100;
    public const PASSWORD_STEP               = 1;

    public function register(): void {
        $this->injectServices();
        $this->registerJavascript();
        $this->registerRoutes();
        $this->registerApiRoutes();
        $this->registerPublicRoutes();
        $this->registerListener();
        $this->registerCommands();
    }

    private function injectServices(): void {
        Keestash::getServer()->register(EncryptionService::class, function () {
            return new EncryptionService(
                Keestash::getServer()->query(IEncryptionService::class)
                , Keestash::getServer()->query(CredentialService::class)
                , Keestash::getServer()->query(ILogger::class)
            );
        });

        Keestash::getServer()->register(NodeService::class, function () {
            return new NodeService(
                Keestash::getServer()->getUserRepository()
                , Keestash::getServer()->query(NodeRepository::class)
                , Keestash::getServer()->query(UserService::class)
            );
        });

        Keestash::getServer()->register(CreateKey::class, function () {
            return new CreateKey(
                Keestash::getServer()->query(KeyService::class)
                , Keestash::getServer()->query(CredentialService::class)
            );
        });

        Keestash::getServer()->register(CreateStarterPassword::class, function () {
            return new CreateStarterPassword(
                Keestash::getServer()->query(Legacy::class)
                , Keestash::getServer()->query(NodeRepository::class)
                , Keestash::getServer()->query(NodeService::class)
                , Keestash::getServer()->query(NodeCredentialService::class)
                , Keestash::getServer()->query(IL10N::class)
            );
        });

        Keestash::getServer()->register(
            BreadCrumbService::class,
            function () {
                return new BreadCrumbService(
                    Keestash::getServer()->getCache()
                    , Keestash::getServer()->query(NodeRepository::class)
                    , Keestash::getServer()->query(IL10N::class)
                );
            }
        );
    }

    private function registerJavascript(): void {
        $this->addJavaScriptFor(
            Application::APP_ID
            , "password_manager"
            , Application::PASSWORD_MANAGER
        );

        $this->addJavaScriptFor(
            Application::APP_ID
            , "public_share"
            , Application::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
        );
    }

    private function registerRoutes(): void {
        $this->registerRoute(
            Application::PASSWORD_MANAGER
            , Controller::class
        );

        $this->registerRoute(
            Application::PASSWORD_MANAGER_ATTACHMENTS_VIEW
            , View::class
        );

        $this->registerRoute(
            Application::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
            , PublicShareController::class
        );
    }

    private function registerApiRoutes(): void {

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_GET_AVATAR
            , \KSA\PasswordManager\Api\Node\Avatar\Get::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_ORGANIZATION_ADD_NODE
            , Organization::class
            , [IRouterManager::POST]
        );

        $this->registerPublicApiRoute(Application::PASSWORD_MANAGER_NODE_GET_AVATAR);

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_ATTACHMENTS_REMOVE
            , \KSA\PasswordManager\Api\Node\Attachment\Remove::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_UPDATE_AVATAR
            , Update::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_ATTACHMENTS_GET
            , \KSA\PasswordManager\Api\Node\Attachment\Get::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_MOVE
            , Move::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_USERS_UPDATE
            , \KSA\PasswordManager\Api\Node\Credential\Update::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_ATTACHMENTS_ADD
            , \KSA\PasswordManager\Api\Node\Attachment\Add::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_CREATE
            , Create::class
            , [IRouterManager::POST]
        );


        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_CREDENTIAL_CREATE
            , \KSA\PasswordManager\Api\Node\Credential\Create::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
            , PublicShareSingle::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_CREDENTIAL_GET
            , \KSA\PasswordManager\Api\Node\Credential\Get::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_SHARE_REMOVE
            , \KSA\PasswordManager\Api\Share\Remove::class
            , [
                IRouterManager::POST
            ]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_SHARE
            , Share::class
            , [
                IRouterManager::POST
            ]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_IMPORT
            , Import::class
            , [
                IRouterManager::POST
            ]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_GET_COMMENT
            , \KSA\PasswordManager\Api\Comment\Get::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_ADD_COMMENT
            , Add::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_DELETE
            , Delete::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_GET
            , Get::class
            , [
                IRouterManager::GET
            ]
        );
        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_NODE_GET_BY_NAME
            , GetByName::class
            , [
                IRouterManager::GET
            ]
        );

        $this->registerPublicApiRoute(Application::PASSWORD_MANAGER_NODE_GET);

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_REMOVE_COMMENT
            , Remove::class
            , [
                IRouterManager::POST
            ]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_SHARE_PUBLIC
            , PublicShare::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_GENERATE_PASSWORD
            , Generate::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::PASSWORD_MANAGER_SHAREABLE_USERS
            , ShareableUsers::class
            , [IRouterManager::GET]
        );
    }

    private function registerPublicRoutes(): void {

        $this->registerPublicApiRoute(
            Application::BREADCRUMB
        );

        $this->registerPublicApiRoute(
            Application::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
        );

        $this->registerPublicRoute(
            Application::PASSWORD_MANAGER_PUBLIC_SHARE_SINGLE
        );
    }

    private function registerListener(): void {
        Keestash::getServer()
            ->getEventManager()
            ->registerListener(
                UserCreatedEvent::class
                , new AfterRegistration(
                    Keestash::getServer()->query(CreateKey::class)
                    , Keestash::getServer()->query(CreateStarterPassword::class)
                )

            );

        Keestash::getServer()
            ->getEventManager()
            ->registerListener(
                UserUpdatedEvent::class
                , new AfterPasswordChanged(
                    Keestash::getServer()->query(IUserKeyRepository::class)
                    , Keestash::getServer()->query(IEncryptionService::class)
                    , Keestash::getServer()->query(CredentialService::class)
                    , Keestash::getServer()->query(ILogger::class)
                )
            );

//        Keestash::getServer()
//            ->getUserStateHookManager()
//            ->addPre(
//                new PreUserDelete(
//                    Keestash::getServer()->query(FileRepository::class)
//                    , Keestash::getServer()->query(IFileRepository::class)
//                )
//            );
//
//        Keestash::getServer()
//            ->getUserStateHookManager()
//            ->addPost(
//                new PostUserDelete(
//                    Keestash::getServer()->query(CommentRepository::class)
//                    , Keestash::getServer()->query(NodeRepository::class)
//                )
//            );
    }

    private function registerCommands(): void {
        $this->registerCommand(
            new CreateFolder(
                Keestash::getServer()->query(NodeRepository::class)
            )
        );

        $this->registerCommand(
            new CreateCredential(
                Keestash::getServer()->query(IUserRepository::class)
                , Keestash::getServer()->query(ValidationService::class)
                , Keestash::getServer()->query(NodeCredentialService::class)
                , Keestash::getServer()->query(NodeRepository::class)
            )
        );
    }

}

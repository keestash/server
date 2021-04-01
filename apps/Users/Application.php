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

namespace KSA\Users;

use Keestash;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use Keestash\Core\Service\User\UserService;
use KSA\Users\Api\File\ProfilePicture;
use KSA\Users\Api\User\GetAll;
use KSA\Users\Api\User\UserEdit;
use KSA\Users\Api\User\UserLock;
use KSA\Users\Api\User\UserRemove;
use KSA\Users\Api\User\UsersAddController;
use KSA\Users\BackgroundJob\UserDeleteTask;
use KSA\Users\Controller\UsersController;
use KSA\Users\Hook\UserState\PostStateChange;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Repository\User\IUserStateRepository;

class Application extends Keestash\App\Application {

    public const TEMPLATE_NAME_USERS = "users.twig";

    public const APP_ID = "users";

    public const USERS                  = "users";
    public const USERS_ADD              = "users/add";
    public const USERS_EDIT             = "users/edit/";
    public const USERS_REMOVE           = "users/remove";
    public const USERS_LOCK             = "users/lock";
    public const USERS_ALL              = "users/all";
    public const USERS_PROFILE_PICTURES = "users/profile_pictures/{token}/{user_hash}/{targetId}/";

    public function register(): void {

        parent::registerRoute(
            Application::USERS
            , UsersController::class
        );

        parent::registerApiRoute(
            Application::USERS_ADD
            , UsersAddController::class
            , [IRouterManager::POST]
        );

        parent::registerApiRoute(
            Application::USERS_EDIT
            , UserEdit::class
            , [IRouterManager::POST]
        );

        parent::registerApiRoute(
            Application::USERS_ALL
            , GetAll::class
            , [IRouterManager::GET]
        );

        parent::registerApiRoute(
            Application::USERS_REMOVE
            , UserRemove::class
            , [IRouterManager::POST]
        );

        parent::registerApiRoute(
            Application::USERS_LOCK
            , UserLock::class
            , [IRouterManager::POST]
        );

        parent::registerApiRoute(
            Application::USERS_PROFILE_PICTURES
            , ProfilePicture::class
            , [IRouterManager::GET]
        );

        parent::addJavascript("users");


        $this->addJavaScriptFor(
            Application::APP_ID
            , "users"
            , Application::USERS
        );

        parent::addSetting(
            self::USERS
            , Keestash::getServer()
            ->getL10N()
            ->translate("Users")
            , "fas fa-user-circle"
            , 1
        );

        $this->addString(
            "profileImage"
            , Keestash::getBaseURL(false) . "/asset/img/profile-picture.png"
        );

        $this->registerServices();
        $this->registerEvents();
    }

    private function registerEvents(): void {
        Keestash::getServer()
            ->getEventManager()
            ->registerListener(
                UserStateDeleteEvent::class
                , new PostStateChange(
                    Keestash::getServer()->query(ILogger::class)
                )
            );
    }

    private function registerServices(): void {
        Keestash::getServer()
            ->register(UserDeleteTask::class, function () {

                /** @var IUserStateRepository $userStateRepository */
                $userStateRepository = Keestash::getServer()
                    ->query(IUserStateRepository::class);

                return new UserDeleteTask(
                    Keestash::getServer()->query(UserService::class)
                    , $userStateRepository->getDeletedUsers()
                    , Keestash::getServer()->query(ConfigService::class)
                    , Keestash::getServer()->query(IUserStateRepository::class)
                    , Keestash::getServer()->getFileLogger()
                );
            });

    }

}

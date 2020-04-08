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

namespace KSA\Users\Application;

use Keestash;
use KSA\Users\Api\ProfilePicture;
use KSA\Users\Api\UsersAddController;
use KSA\Users\Controller\UsersController;
use KSP\Core\Manager\RouterManager\IRouterManager;

class Application extends Keestash\App\Application {

    public const PERMISSION_USERS = "users";

    public const TEMPLATE_NAME_ALL_USERS = "all_users.twig";

    public const APP_ID                 = "users";
    public const USERS_ADD              = "users/add";
    public const USERS                  = "users";
    public const USERS_PROFILE_PICTURES = "users/profile_pictures/{token}/{user_hash}/";

    public function register(): void {

        parent::registerRoute(
            Application::USERS
            , UsersController::class
        );

        parent::registerRoute(
            Application::USERS_ADD
            , UsersAddController::class
            , [IRouterManager::POST]
        );

        parent::registerApiRoute(
            Application::USERS_PROFILE_PICTURES
            , ProfilePicture::class
            , [IRouterManager::GET]
        );

        parent::addJavascript("all_users");


        $this->addJavaScriptFor(
            Application::APP_ID
            , "all_users"
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
    }

}
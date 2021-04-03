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
use KSA\Users\Controller\UsersController;

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

    }


}

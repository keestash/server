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

namespace KSA\Login\Application;

use Keestash\Core\Manager\RouterManager\RouterManager;
use KSA\Login\Api\LoginService;
use KSA\Login\Controller\LoginController;

class Application extends \Keestash\App\Application {

    public const PERMISSION_LOGIN        = "login";
    public const PERMISSION_LOGIN_SUBMIT = "login_submit";
    public const LOGIN                   = "login";
    public const LOGIN_SUBMIT            = "login/submit";

    public const APP_NAME_REGISTER = "register";

    public function register(): void {

        parent::registerRoute(
            self::LOGIN
            , LoginController::class
            , [RouterManager::GET]
        );

        parent::registerApiRoute(
            self::LOGIN_SUBMIT
            , LoginService::class
            , [RouterManager::POST]
        );

        parent::registerPublicRoute(
            self::LOGIN
        );

        parent::registerPublicApiRoute(
            self::LOGIN_SUBMIT
        );

        parent::addJavascript(
            self::LOGIN
        );

    }


}
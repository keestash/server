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

use Keestash;
use KSA\Login\Api\Login;
use KSA\Login\Controller\LoginController;
use KSA\Login\Service\TokenService;
use KSP\Core\Manager\RouterManager\IRouterManager;

class Application extends \Keestash\App\Application {

    public const PERMISSION_LOGIN        = "login";
    public const PERMISSION_LOGIN_SUBMIT = "login_submit";
    public const LOGIN                   = "login";
    public const LOGIN_SUBMIT            = "login/submit";

    public const APP_NAME_REGISTER = "register";

    public function register(): void {

        parent::registerRoute(
            Application::LOGIN
            , LoginController::class
            , [IRouterManager::GET]
        );

        parent::registerApiRoute(
            Application::LOGIN_SUBMIT
            , Login::class
            , [IRouterManager::POST]
        );

        parent::registerPublicRoute(
            Application::LOGIN
        );

        parent::registerPublicApiRoute(
            Application::LOGIN_SUBMIT
        );

        parent::addJavascript(
            Application::LOGIN
        );

        $this->registerServices();
    }

    private function registerServices(): void {
        Keestash::getServer()->register(TokenService::class, function () {
            return new TokenService();
        }
        );
    }

}

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

namespace KSA\Register\Application;

use Keestash;
use KSA\Register\Controller\Controller;

class Application extends Keestash\App\Application {

    public const APP_NAME_REGISTER = "register";

    public const PERMISSION_REGISTER = "register";
    public const REGISTER            = "register";
    public const REGISTER_ADD        = "register/add/";
    public const USER_EXISTS         = "user/exists/{userName}/";
    public const MAIL_EXISTS         = "user/mail/exists/{address}/";

    public function register(): void {

        $this->addJavascript(
            Application::REGISTER
        );

        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        $this->registerRoute(
            Application::REGISTER
            , Controller::class
        );
        $this->registerPublicRoute(Application::REGISTER);
    }

}


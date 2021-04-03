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

namespace KSA\ForgotPassword\Application;

use KSA\ForgotPassword\Controller\ForgotPassword;
use KSA\ForgotPassword\Controller\ResetPassword;

class Application extends \Keestash\App\Application {

    public const FORGOT_PASSWORD = "forgot_password";
    public const RESET_PASSWORD  = "reset_password/{token}/";

    public function register(): void {

        parent::registerRoute(
            self::FORGOT_PASSWORD
            , ForgotPassword::class
        );

        parent::registerRoute(
            self::RESET_PASSWORD
            , ResetPassword::class
        );

        parent::registerPublicRoute(
            self::FORGOT_PASSWORD
        );

        parent::registerPublicRoute(
            self::RESET_PASSWORD
        );

        parent::addJavascript(
            self::FORGOT_PASSWORD
        );

        parent::addJavaScriptFor(
            "forgot_password"
            , "reset_password"
            , self::RESET_PASSWORD
        );

    }

}
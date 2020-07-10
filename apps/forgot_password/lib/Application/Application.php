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
use KSP\Core\Manager\RouterManager\IRouterManager;

class Application extends \Keestash\App\Application {

    public const PERMISSION_FORGOT_PASSWORD        = "forgot_password";
    public const PERMISSION_RESET_PASSWORD         = "reset_password";
    public const PERMISSION_FORGOT_PASSWORD_SUBMIT = "forgot_password_submit";
    public const FORGOT_PASSWORD_SUBMIT            = "forgot_password/submit";
    public const FORGOT_PASSWORD                   = "forgot_password";
    public const RESET_PASSWORD                    = "reset_password/{token}/";
    public const RESET_PASSWORD_UPDATE             = "/reset_password/update/";

    public function register(): void {


        parent::registerRoute(
            self::FORGOT_PASSWORD
            , ForgotPassword::class
        );

        parent::registerRoute(
            self::RESET_PASSWORD
            , ResetPassword::class
        );

        parent::registerApiRoute(
            self::FORGOT_PASSWORD_SUBMIT
            , \KSA\ForgotPassword\Api\ForgotPassword::class
            , [IRouterManager::POST]
        );

        parent::registerPublicApiRoute(
            self::FORGOT_PASSWORD_SUBMIT
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

        $this->registerApiRoute(
            Application::RESET_PASSWORD_UPDATE
            , \KSA\ForgotPassword\Api\ResetPassword::class
            , [IRouterManager::POST]
        );
        $this->registerPublicApiRoute(
            Application::RESET_PASSWORD_UPDATE
        );

        parent::addJavaScriptFor(
            "forgot_password"
            , "reset_password"
            , self::RESET_PASSWORD
        );

    }

    public function isActive(): bool {
        return true;
    }

}
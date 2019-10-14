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

namespace KSA\Account\Application;

use Keestash;
use Keestash\Core\Manager\RouterManager\RouterManager;
use KSA\Account\Api\DeleteProfileImage;
use KSA\Account\Api\UpdatePassword;
use KSA\Account\Api\UpdateProfileImage;
use KSA\Account\Api\UpdateUserData;
use KSA\Account\Controller\AccountController;

class Application extends Keestash\App\Application {

    public const APP_ID                          = "account";
    public const PERMISSION_ACCOUNT              = "account";
    public const PERMISSION_SECURITY             = "security";
    public const PERMISSION_DELETE_PROFILE_IMAGE = "delete_profile_image";
    public const PERMISSION_UPDATE_PASSWORD      = "update_password";
    public const PERMISSION_UPDATE_PROFILE_IMAGE = "update_profile_image";
    public const PERMISSION_UPDATE_USER_DATA     = "update_user_data";
    public const ACCOUNT_PROFILE_UPDATE          = "account/profile/update/";
    public const ACCOUNT                         = "account";
    public const ACCOUNT_PROFILE_IMAGE_DELETE    = "account/profile/image/delete/";
    public const ACCOUNT_PROFILE_IMAGE_UPDATE    = "account/profile/image/update/";
    public const ACCOUNT_SINGLE                  = "account/list/{id}/";
    public const SECURITY_PASSWORD_UPDATE        = "security/password/update/";
    public const SECURITY                        = "security";

    public function register(): void {

        parent::registerRoute(
            self::ACCOUNT
            , AccountController::class
        );

        parent::registerRoute(
            self::SECURITY
            , AccountController::class
        );

        parent::registerApiRoute(
            self::ACCOUNT_SINGLE
            , AccountController::class
            , [RouterManager::GET]
        );

        parent::registerApiRoute(
            self::ACCOUNT_PROFILE_UPDATE
            , UpdateUserData::class
            , [RouterManager::POST]
        );

        parent::registerApiRoute(
            self::ACCOUNT_PROFILE_IMAGE_UPDATE
            , UpdateProfileImage::class
            , [RouterManager::POST]
        );

        parent::registerApiRoute(
            self::ACCOUNT_PROFILE_IMAGE_DELETE
            , DeleteProfileImage::class
            , [RouterManager::POST]
        );

        parent::registerApiRoute(
            self::SECURITY_PASSWORD_UPDATE
            , UpdatePassword::class
            , [RouterManager::POST]
        );

        parent::addJavascript(self::ACCOUNT);
        parent::addJavascriptFor(self::ACCOUNT, self::SECURITY, Application::SECURITY);

        parent::addSetting(
            self::ACCOUNT
            , Keestash::getServer()
            ->getL10N()
            ->translate("Account")
            , "fas fa-user-circle"
            , 0
        );

    }

}
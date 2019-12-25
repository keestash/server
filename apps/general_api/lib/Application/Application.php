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

namespace KSA\GeneralApi\Application;

use Keestash\Core\Manager\RouterManager\RouterManager;
use KSA\general_api\lib\Api\UserList;
use KSA\GeneralApi\Api\Thumbnail\File;
use KSA\GeneralApi\Api\MinimumCredential;

/**
 * Class Application
 * @package Keestash\Api\Core
 */
class Application extends \Keestash\App\Application {

    public const PASSWORD_REQUIREMENTS = "password_requirements/";
    public const ALL_USERS             = "users/all/{type}/";
    public const FILE_ICONS            = "icon/file/get/{extension}/";


    public function register(): void {

        parent::registerApiRoute(
            Application::PASSWORD_REQUIREMENTS
            , MinimumCredential::class
            , [RouterManager::POST]
        );

        parent::registerApiRoute(
            Application::ALL_USERS
            , UserList::class
            , [RouterManager::GET]
        );


        $this->registerApiRoute(
            Application::FILE_ICONS
            , File::class
            , [RouterManager::GET]
        );

        parent::registerPublicApiRoute(
            Application::PASSWORD_REQUIREMENTS
        );

        parent::registerPublicApiRoute(
            Application::FILE_ICONS
        );

    }

}
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

use Keestash;
use KSA\GeneralApi\Controller\Organization\Detail;
use KSA\GeneralApi\Controller\Route\RouteList;

/**
 * Class Application
 *
 * @package Keestash\Api\Core
 */
class Application extends Keestash\App\Application {

    public const APP_ID = 'general_api';

    public const PASSWORD_REQUIREMENTS    = "password_requirements/";
    public const ALL_USERS                = "users/all/{type}/";
    public const FILE_ICONS               = "icon/file/get/{extension}/";
    public const FRONTEND_TEMPLATES       = "frontend_templates/all/";
    public const FRONTEND_STRINGS         = "frontend_strings/all/";
    public const ROUTE_LIST               = "route_list/all/";
    public const ORGANIZATION_LIST        = "organizations/all/";
    public const ORGANIZATION_ADD         = "organizations/add/";
    public const ORGANIZATION_SINGLE      = "organizations/{id}/";
    public const ORGANIZATION_UPDATE      = "organizations/update/";
    public const ORGANIZATION_USER_CHANGE = "organizations/user/change/";
    public const DEMOUSERS_USER_ADD       = "demousers/user/add/";

    public function register(): void {
        $this->registerRoutes();
        $this->registerJavascript();
    }


    private function registerJavascript(): void {
        $this->addJavaScriptFor(
            Application::APP_ID
            , "organization_detail"
            , Application::ORGANIZATION_SINGLE
        );
    }

    private function registerRoutes(): void {
        $this->registerRoute(
            Application::ROUTE_LIST
            , RouteList::class
        );

        $this->registerRoute(
            Application::ORGANIZATION_SINGLE
            , Detail::class
        );
    }

}

<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Apps\ConfigProvider;
use KSA\Apps\Controller\Controller;
use KSP\Api\IRoute;

return [
    CoreConfigProvider::ROUTES                 => [
        [
            IRoute::PATH         => ConfigProvider::ROUTE_NAME_APPS
            , IRoute::MIDDLEWARE => Controller::class
            , IRoute::NAME       => Controller::class
        ],
    ],
    CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::ROUTE_NAME_APPS => 'apps'
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
        ConfigProvider::ROUTE_NAME_APPS => 'apps'
    ],
    CoreConfigProvider::SETTINGS               => [
        ConfigProvider::ROUTE_NAME_APPS => [
            CoreConfigProvider::SETTINGS_NAME    => 'Apps'
            , 'faClass'                          => "fas fa-user-circle"
            , CoreConfigProvider::SETTINGS_ORDER => 2
        ]
    ]
];
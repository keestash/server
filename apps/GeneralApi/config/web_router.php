<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

use Keestash\ConfigProvider as ConfigProviderAlias;
use KSA\GeneralApi\ConfigProvider;
use KSA\GeneralApi\Controller\Common\DefaultRouteController;
use KSA\GeneralApi\Controller\Route\RouteList;

return [
    ConfigProviderAlias::ROUTES                   => [
        [
            'path'         => ConfigProvider::ROUTE_LIST_ALL
            , 'middleware' => RouteList::class
            , 'name'       => RouteList::class
        ],
        [
            'path'         => ConfigProvider::DEFAULT
            , 'middleware' => DefaultRouteController::class
            , 'name'       => ConfigProvider::DEFAULT
        ],
        [
            'path'         => ConfigProvider::DEFAULT_SLASH
            , 'middleware' => DefaultRouteController::class
            , 'name'       => ConfigProvider::DEFAULT_SLASH
        ],
    ]
    , ConfigProviderAlias::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::ROUTE_LIST_ALL => 'route_list'
    ],
];
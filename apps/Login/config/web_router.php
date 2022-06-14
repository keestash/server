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

use Keestash\ConfigProvider as CoreConfigProvider;
use KSA\Login\ConfigProvider;
use KSA\Login\Controller\Login;
use KSA\Login\Controller\Logout;
use KSP\Api\IRoute;

return [
    CoreConfigProvider::ROUTES                 => [
        [
            IRoute::PATH         => ConfigProvider::LOGOUT
            , IRoute::MIDDLEWARE => Logout::class
            , IRoute::NAME       => Logout::class
        ],
        [
            IRoute::PATH         => ConfigProvider::LOGIN
            , IRoute::MIDDLEWARE => Login::class
            , IRoute::NAME       => Login::class
        ],
    ],
    CoreConfigProvider::PUBLIC_ROUTES          => [
        ConfigProvider::LOGIN
    ],
    CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::LOGIN => 'login'
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS     => [
        ConfigProvider::LOGIN    => 'login'
        , ConfigProvider::LOGOUT => 'logout'
    ],
    CoreConfigProvider::SETTINGS               => [
        ConfigProvider::LOGOUT => [
            CoreConfigProvider::SETTINGS_NAME    => 'Logout'
            , 'faClass'                          => "fas fa-sign-out-alt"
            , CoreConfigProvider::SETTINGS_ORDER => 4
        ]
    ]
];
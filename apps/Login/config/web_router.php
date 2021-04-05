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

use KSA\Login\ConfigProvider;
use KSA\Login\Controller\LoginController;
use KSA\Login\Controller\Logout;
use KSP\App\IApp;

return [
    IApp::CONFIG_PROVIDER_ROUTES                 => [
        [
            'path'         => ConfigProvider::LOGOUT
            , 'middleware' => Logout::class
            , 'name'       => Logout::class
        ],
        [
            'path'         => ConfigProvider::LOGIN
            , 'middleware' => LoginController::class
            , 'name'       => LoginController::class
        ],
    ],
    IApp::CONFIG_PROVIDER_PUBLIC_ROUTES          => [
        ConfigProvider::LOGOUT
        , ConfigProvider::LOGIN
    ],
    IApp::CONFIG_PROVIDER_WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::LOGIN => 'login'
    ],
    IApp::CONFIG_PROVIDER_WEB_ROUTER_SCRIPTS     => [
        ConfigProvider::LOGIN => 'login'
        ,ConfigProvider::LOGOUT => 'logout'
    ],
    IApp::CONFIG_PROVIDER_SETTINGS               => [
        ConfigProvider::LOGOUT => [
            'name'      => 'logout'
            , 'faClass' => "fas fa-sign-out-alt"
            , 'order'   => 5
        ]
    ]
];
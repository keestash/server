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
use KSA\Profile\ConfigProvider;

return [
    CoreConfigProvider::ROUTES                   => [
        [
            'path'         => ConfigProvider::PROFILE
            , 'middleware' => \KSA\Profile\Controller\Controller::class
            , 'name'       => \KSA\Profile\Controller\Controller::class
        ]
    ]
    , CoreConfigProvider::WEB_ROUTER_STYLESHEETS => [
        ConfigProvider::PROFILE => 'style'
    ],
    CoreConfigProvider::WEB_ROUTER_SCRIPTS       => [
        ConfigProvider::PROFILE => 'profile'
    ],
    CoreConfigProvider::SETTINGS                 => [
        ConfigProvider::PROFILE => [
            CoreConfigProvider::SETTINGS_NAME    => 'Profile'
            , 'faClass'                          => "fas fa-user-alt"
            , CoreConfigProvider::SETTINGS_ORDER => 0
        ]
    ]
];
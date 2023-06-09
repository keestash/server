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
use KSA\Instance\ConfigProvider;

return [
    CoreConfigProvider::DEPENDENCIES    => require __DIR__ . '/dependencies.php'
    , CoreConfigProvider::API_ROUTER    => require __DIR__ . '/api_router.php'
    , CoreConfigProvider::PERMISSIONS   => require __DIR__ . '/permissions.php'
    , CoreConfigProvider::COMMANDS      => require __DIR__ . '/commands.php'
    , CoreConfigProvider::APP_LIST      => [
        ConfigProvider::APP_ID => [
            CoreConfigProvider::APP_ORDER     => 2
            , CoreConfigProvider::APP_NAME    => 'Instance'
            , CoreConfigProvider::APP_VERSION => 1,
        ],
    ]
    , CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::INSTALL_INSTANCE_END_UPDATE
        , ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
        , ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
    ]
];
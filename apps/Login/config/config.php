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

use Keestash\ConfigProvider;
use KSA\Login\ConfigProvider as LoginConfigProvider;

return [
    ConfigProvider::DEPENDENCIES  => require __DIR__ . '/dependencies.php'
    , ConfigProvider::API_ROUTER  => require __DIR__ . '/api_router.php'
    , ConfigProvider::PERMISSIONS => require __DIR__ . '/permissions.php'
    , ConfigProvider::COMMANDS    => require __DIR__ . '/commands.php'
    , ConfigProvider::APP_LIST    => [
        LoginConfigProvider::APP_ID => [
            ConfigProvider::APP_ORDER        => 3
            , ConfigProvider::APP_NAME       => 'Login'
            , ConfigProvider::APP_BASE_ROUTE => LoginConfigProvider::LOGIN
            , ConfigProvider::APP_VERSION    => 1
        ],
    ]
];
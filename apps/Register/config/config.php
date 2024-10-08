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
use KSA\Register\ConfigProvider;

return [
    CoreConfigProvider::APP_LIST         => [
        ConfigProvider::APP_ID => [
            CoreConfigProvider::APP_ORDER   => 6,
            CoreConfigProvider::APP_NAME    => 'Register',
            CoreConfigProvider::APP_VERSION => 1,
        ],
    ]
    , CoreConfigProvider::DEPENDENCIES   => require __DIR__ . '/dependencies.php'
    , CoreConfigProvider::API_ROUTER     => require __DIR__ . '/api_router.php'
    , CoreConfigProvider::EVENTS         => require __DIR__ . '/events.php'
    , CoreConfigProvider::COMMANDS       => require __DIR__ . '/commands.php'
    , CoreConfigProvider::PERMISSIONS    => require __DIR__ . '/permissions.php'
    , CoreConfigProvider::RESPONSE_CODES => require __DIR__ . '/response_codes.php'
    , CoreConfigProvider::TEMPLATES      => [
        'paths' => [
            'register' => [__DIR__ . '/../template/email']
        ]
    ]
];
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

return [
    ConfigProvider::DEPENDENCIES                                        => require __DIR__ . '/dependencies.php',
    ConfigProvider::API_ROUTER                                          => require __DIR__ . '/api_router.php',
    ConfigProvider::EVENTS                                              => require __DIR__ . '/events.php',
    ConfigProvider::COMMANDS                                            => require __DIR__ . '/commands.php',
    ConfigProvider::PERMISSIONS                                         => require __DIR__ . '/permissions.php',
    ConfigProvider::RESPONSE_CODES                                      => require __DIR__ . '/response_codes.php',
    \KSA\PasswordManager\ConfigProvider::FILE_UPLOAD_ALLOWED_EXTENSIONS => require __DIR__ . '/allowed_extensions.php',
    ConfigProvider::TEMPLATES                                           => [
        'paths' => [
            'passwordManagerEmail' => [__DIR__ . '/../template/email']
        ]
    ],
    ConfigProvider::APP_LIST                                            => [
        \KSA\PasswordManager\ConfigProvider::APP_ID => [
            ConfigProvider::APP_ORDER   => 0,
            ConfigProvider::APP_NAME    => 'Password Manager',
            ConfigProvider::APP_VERSION => 1,
        ],
    ],

];
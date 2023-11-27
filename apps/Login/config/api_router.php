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
use KSA\Login\Api\Configuration;
use KSA\Login\Api\Key\Key;
use KSA\Login\Api\Login\Login;
use KSA\Login\Api\Logout;
use KSA\Login\ConfigProvider;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::ROUTES        => [
        [
            IRoute::PATH         => ConfigProvider::LOGIN_SUBMIT
            , IRoute::MIDDLEWARE => Login::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Login::class
        ],
        [
            IRoute::PATH         => ConfigProvider::LOGOUT_SUBMIT
            , IRoute::MIDDLEWARE => Logout::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Logout::class
        ],
        [
            IRoute::PATH         => ConfigProvider::APP_CONFIGURATION
            , IRoute::MIDDLEWARE => Configuration::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Configuration::class
        ],
        [
            IRoute::PATH         => ConfigProvider::LOGIN_KEY
            , IRoute::MIDDLEWARE => Key::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Key::class
        ],
    ],
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::LOGIN_SUBMIT
        , ConfigProvider::APP_CONFIGURATION
    ]
];
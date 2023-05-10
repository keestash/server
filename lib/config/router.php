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

use Keestash\Api\PingHandler;
use Keestash\ConfigProvider;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    ConfigProvider::ROUTES        => [
        [
            IRoute::PATH         => ConfigProvider::PING_ROUTE
            , IRoute::MIDDLEWARE => PingHandler::class
            , IRoute::NAME       => PingHandler::class
            , IRoute::METHOD     => IVerb::GET
        ]
    ],
    ConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::PING_ROUTE
    ]
];
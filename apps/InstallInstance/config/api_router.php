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
use KSA\InstallInstance\Api\Config\Get;
use KSA\InstallInstance\Api\Config\Update;
use KSA\InstallInstance\Api\EndUpdate\EndUpdate;
use KSA\InstallInstance\ConfigProvider;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::ROUTES                    => [
        [
            IRoute::PATH         => ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
            , IRoute::MIDDLEWARE => Update::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Update::class
        ],
        [
            IRoute::PATH         => ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
            , IRoute::MIDDLEWARE => Get::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Get::class
        ],
        [
            IRoute::PATH         => ConfigProvider::INSTALL_INSTANCE_END_UPDATE
            , IRoute::MIDDLEWARE => EndUpdate::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => EndUpdate::class
        ],
    ]
    , CoreConfigProvider::INSTALL_INSTANCE_ROUTES => [
        ConfigProvider::INSTALL_INSTANCE_UPDATE_CONFIG
        , ConfigProvider::INSTALL_INSTANCE_CONFIG_DATA
        , ConfigProvider::INSTALL_INSTANCE_END_UPDATE
    ]
];
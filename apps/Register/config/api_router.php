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
use KSA\Register\Api\Configuration\Configuration;
use KSA\Register\Api\MinimumCredential;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Confirm;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\ConfigProvider;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::REGISTER_ADD
        , ConfigProvider::PASSWORD_REQUIREMENTS
        , ConfigProvider::REGISTER_CONFIGURATION
        , ConfigProvider::REGISTER_CONFIRM
    ],
    CoreConfigProvider::ROUTES        => [
        [
            IRoute::PATH         => ConfigProvider::REGISTER_ADD
            , IRoute::MIDDLEWARE => Add::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::REGISTER_CONFIRM
            , IRoute::MIDDLEWARE => Confirm::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Confirm::class
        ],
        [
            IRoute::PATH         => ConfigProvider::USER_EXISTS_BY_MAIL
            , IRoute::MIDDLEWARE => MailExists::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => MailExists::class
        ],
        [
            IRoute::PATH         => ConfigProvider::USER_EXISTS_BY_USERNAME
            , IRoute::MIDDLEWARE => Exists::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Exists::class
        ],
        [
            IRoute::PATH         => ConfigProvider::PASSWORD_REQUIREMENTS
            , IRoute::MIDDLEWARE => MinimumCredential::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => MinimumCredential::class
        ],
        [
            IRoute::PATH         => ConfigProvider::REGISTER_CONFIGURATION
            , IRoute::MIDDLEWARE => Configuration::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Configuration::class
        ],
    ]
];
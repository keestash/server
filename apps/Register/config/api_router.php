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
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Confirm;
use KSA\Register\Api\User\ResetPassword;
use KSA\Register\Api\User\ResetPasswordConfirm;
use KSA\Register\Api\User\ResetPasswordRetrieve;
use KSA\Register\ConfigProvider;
use KSA\Register\Middleware\RegisterEnabledMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    CoreConfigProvider::PUBLIC_ROUTES => [
        ConfigProvider::REGISTER_ADD
        , ConfigProvider::REGISTER_CONFIGURATION
        , ConfigProvider::REGISTER_CONFIRM
        , ConfigProvider::RESET_PASSWORD
        , ConfigProvider::RESET_PASSWORD_RETRIEVE
        , ConfigProvider::RESET_PASSWORD_CONFIRM
    ],
    CoreConfigProvider::ROUTES        => [
        [
            IRoute::PATH         => ConfigProvider::RESET_PASSWORD
            , IRoute::MIDDLEWARE => ResetPassword::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => ResetPassword::class
        ],
        [
            IRoute::PATH         => ConfigProvider::REGISTER_ADD
            , IRoute::MIDDLEWARE => [RegisterEnabledMiddleware::class, Add::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Add::class
        ],
        [
            IRoute::PATH         => ConfigProvider::REGISTER_CONFIRM
            , IRoute::MIDDLEWARE => [RegisterEnabledMiddleware::class, Confirm::class]
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => Confirm::class
        ],
        [
            IRoute::PATH         => ConfigProvider::REGISTER_CONFIGURATION
            , IRoute::MIDDLEWARE => [RegisterEnabledMiddleware::class, Configuration::class]
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => Configuration::class
        ],
        [
            IRoute::PATH         => ConfigProvider::RESET_PASSWORD_CONFIRM
            , IRoute::MIDDLEWARE => ResetPasswordConfirm::class
            , IRoute::METHOD     => IVerb::POST
            , IRoute::NAME       => ResetPasswordConfirm::class
        ],
        [
            IRoute::PATH         => ConfigProvider::RESET_PASSWORD_RETRIEVE
            , IRoute::MIDDLEWARE => ResetPasswordRetrieve::class
            , IRoute::METHOD     => IVerb::GET
            , IRoute::NAME       => ResetPasswordRetrieve::class
        ]
    ]
];
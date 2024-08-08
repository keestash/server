<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

use Keestash\Middleware\DeactivatedRouteMiddleware;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\Add;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\Delete;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\Get;
use KSA\PasswordManager\Api\Node\Credential\AdditionalData\GetValue;
use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\Api\Node\Credential\Generate\Generate;
use KSA\PasswordManager\Api\Node\Credential\Generate\Quality;
use KSA\PasswordManager\Api\Node\Credential\ListAll;
use KSA\PasswordManager\Api\Node\Credential\Password\Update;
use KSA\PasswordManager\Api\Node\Pwned\ChangeState;
use KSA\PasswordManager\Api\Node\Pwned\ChartData;
use KSA\PasswordManager\Api\Node\Pwned\IsActive;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_GENERATE_PASSWORD
        , IRoute::MIDDLEWARE => Generate::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => Generate::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_GENERATE_QUALITY
        , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, Quality::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => Quality::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_CREATE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Create::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Create::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_ALL
        , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, ChartData::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => ChartData::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_CREDENTIAL_PASSWORD_GET_BY_NODE_ID
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Password\Get::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Password\Get::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_CREDENTIAL_GET_BY_NODE_ID
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Get::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Get::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, \KSA\PasswordManager\Api\Node\Credential\Update::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => \KSA\PasswordManager\Api\Node\Credential\Update::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_PASSWORD_UPDATE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Update::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Update::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHANGE_STATE
        , IRoute::MIDDLEWARE => ChangeState::class
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => ChangeState::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_IS_ACTIVE
        , IRoute::MIDDLEWARE => IsActive::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => IsActive::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET_VALUE
        , IRoute::MIDDLEWARE => GetValue::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => GetValue::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET
        , IRoute::MIDDLEWARE => Get::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => Get::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_ADD
        , IRoute::MIDDLEWARE => Add::class
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Add::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_DELETE
        , IRoute::MIDDLEWARE => Delete::class
        , IRoute::METHOD     => IVerb::DELETE
        , IRoute::NAME       => Delete::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_LIST_ALL
        , IRoute::MIDDLEWARE => ListAll::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => ListAll::class
    ],

];

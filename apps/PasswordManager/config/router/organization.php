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
use KSA\PasswordManager\Api\Node\Organization\Add as AddOrganization;
use KSA\PasswordManager\Api\Node\Organization\Remove;
use KSA\PasswordManager\Api\Node\Organization\Update;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_ADD
        , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, AddOrganization::class]
        , IRoute::METHOD     => IVerb::PUT
        , IRoute::NAME       => AddOrganization::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_UPDATE
        , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Update::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Update::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_ORGANIZATION_NODE_REMOVE
        , IRoute::MIDDLEWARE => [DeactivatedRouteMiddleware::class, NodeAccessMiddleware::class, Remove::class]
        , IRoute::METHOD     => IVerb::DELETE
        , IRoute::NAME       => Remove::class
    ],

];

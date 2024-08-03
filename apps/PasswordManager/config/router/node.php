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

use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Api\Node\Folder\Create;
use KSA\PasswordManager\Api\Node\Folder\Update;
use KSA\PasswordManager\Api\Node\Get\Get as NodeGet;
use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Api\Node\Search;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_DELETE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Delete::class]
        , IRoute::METHOD     => IVerb::DELETE
        , IRoute::NAME       => Delete::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_ID
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, NodeGet::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => NodeGet::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_NAME
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, GetByName::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => GetByName::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_MOVE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Move::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Move::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_UPDATE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Update::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Update::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_NODE_CREATE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Create::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Create::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_SEARCH
        , IRoute::MIDDLEWARE => Search::class
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => Search::class
    ],

];

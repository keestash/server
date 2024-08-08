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

use KSA\PasswordManager\Api\Node\Share\Public\PublicShare;
use KSA\PasswordManager\Api\Node\Share\Public\PublicShareSingle;
use KSA\PasswordManager\Api\Node\Share\Regular\Remove as RemoveShare;
use KSA\PasswordManager\Api\Node\Share\Regular\Share;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Middleware\NodeAccessMiddleware;
use KSP\Api\IRoute;
use KSP\Api\IVerb;

return [
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_DECRYPT
        , IRoute::MIDDLEWARE => [PublicShareSingle::class]
        , IRoute::METHOD     => IVerb::GET
        , IRoute::NAME       => PublicShareSingle::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, PublicShare::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => PublicShare::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, PublicShare::class]
        , IRoute::METHOD     => IVerb::DELETE
        , IRoute::NAME       => PublicShare::class . '@' . IVerb::DELETE
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_REMOVE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, RemoveShare::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => RemoveShare::class
    ],
    [
        IRoute::PATH         => ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE
        , IRoute::MIDDLEWARE => [NodeAccessMiddleware::class, Share::class]
        , IRoute::METHOD     => IVerb::POST
        , IRoute::NAME       => Share::class
    ],

];

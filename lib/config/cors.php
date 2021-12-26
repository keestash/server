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

use Keestash\Core\Service\Router\Verification;
use Mezzio\Cors\Configuration\ConfigurationInterface;

return
    [
        'allowed_origins'     => [ConfigurationInterface::ANY_ORIGIN], // Allow any origin
        'exposed_headers'     => [
            Verification::FIELD_NAME_TOKEN
            , Verification::FIELD_NAME_USER_HASH
        ],
        'allowed_methods'     => ["GET", "PUT", "POST", "DELETE", "HEAD", "OPTIONS"],
        'allowed_max_age'     => '600', // 10 minutes
        'credentials_allowed' => true, // Allow cookies
        'allowed_headers'     => [
            'Access-Control-Allow-Headers'
            , 'Access-Control-Allow-Origin'
            , 'Access-Control-Request-Headers'
            , 'Content-Type'
            , 'Origin'
            , 'X-Requested-With'
            , 'Accept'
            , 'DNT'
            , 'Referer'
            , 'User-Agent'
            , Verification::FIELD_NAME_TOKEN
            , Verification::FIELD_NAME_USER_HASH
            , 'Authorization'
        ], // Tell client that the API will always return this header
    ];

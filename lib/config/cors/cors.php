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

use KSP\Api\IResponse;

return
    [
        'allowed_origins'     => require_once __DIR__ . '/allowed_origins.php',
        'exposed_headers'     => [
            IResponse::HEADER_X_KEESTASH_TOKEN
            , IResponse::HEADER_X_KEESTASH_USER
            , IResponse::HEADER_X_KEESTASH_AUTHENTICATION
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
            , IResponse::HEADER_X_KEESTASH_TOKEN
            , IResponse::HEADER_X_KEESTASH_USER
            , 'Authorization'
        ], // Tell client that the API will always return this header
    ];

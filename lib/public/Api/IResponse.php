<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSP\Api;

interface IResponse {

    public const HEADER_CONTENT_TYPE                        = "Content-Type";
    public const HEADER_X_KEESTASH_AUTHENTICATION           = "x-keestash-authentication";
    public const HEADER_X_KEESTASH_USER                     = "x-keestash-user";
    public const HEADER_X_KEESTASH_TOKEN                    = "x-keestash-token";
    public const HEADER_CONTENT_SECURITY_POLICY             = "Content-Security-Policy";
    public const HEADER_CONTENT_SECURITY_POLICY_REPORT_ONLY = "Content-Security-Policy-Report-Only";

    public const OK                    = 200;
    public const NOT_MODIFIED          = 304;
    public const BAD_REQUEST           = 400;
    public const UNAUTHORIZED          = 401;
    public const FORBIDDEN             = 403;
    public const NOT_FOUND             = 404;
    public const NOT_ALLOWED           = 405;
    public const NOT_ACCEPTABLE        = 406;
    public const CONFLICT              = 409;
    public const TOO_MANY_REQUESTS     = 429;
    public const INTERNAL_SERVER_ERROR = 500;
    public const SERVICE_UNAVAILABLE   = 503;


}
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

namespace Keestash\Core\DTO;

/**
 * Class HTTP
 * @package Keestash\Core\DTO
 * @deprecated remove
 */
class HTTP {

    // Success
    public const OK = 200;

    // Client
    public const BAD_REQUEST = 400;
    public const NOT_FOUND   = 404;

    private function __construct() {
    }

    public static function getDescriptionByCode(int $code): ?string {
        if ($code === HTTP::OK) {
            return "OK";
        } else {
            if ($code === HTTP::BAD_REQUEST) {
                return "BAD REQUEST";
            } else {
                if ($code === HTTP::NOT_FOUND) {
                    return "NOT FOUND";
                }
            }
        }
        return null;
    }

}
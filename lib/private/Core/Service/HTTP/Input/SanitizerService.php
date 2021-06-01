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

namespace Keestash\Core\Service\HTTP\Input;

use HTMLPurifier;

/**
 * Class SanitizerService
 * @package Keestash\Core\Service\HTTP\Input
 */
class SanitizerService {

    private HTMLPurifier $purifier;

    public function __construct(HTMLPurifier $purifier) {
        $this->purifier = $purifier;
    }

    /**
     * sanitizes HTTP input (usually from $_GET, $_POST, etc)
     *
     * @param string $input The input value
     * @return string The sanitized input value
     *
     */
    public function sanitize(string $input): string {
        return $this->purifier->purify($input);
    }

    /**
     * sanitizes an array of HTTP input (usually from $_GET, $_POST, etc)
     *
     * @param array $all The input array containing all values
     * @return array The sanitized input array
     *
     * TODO implement
     */
    public function sanitizeAll(array $all): array {
        foreach ($all as $key => $input) {
            $all[$key] = $input;
        }

        return $all;
    }

}
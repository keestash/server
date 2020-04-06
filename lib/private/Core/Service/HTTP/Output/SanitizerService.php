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

namespace Keestash\Core\Service\HTTP\Output;


/**
 * Class SanitizerService
 * @package Keestash\Core\Service\HTTP\Input
 */
class SanitizerService {

    /**
     * sanitizes output (usually strips JavaScript and other potentially risky code)
     *
     * @param string $output The output intended for the client
     * @return string The sanitized output value
     *
     * TODO implement
     */
    public function sanitize(string $output): string {
        return $output;
    }

    /**
     * sanitizes an array of output values (usually strips JavaScript and other potentially risky code)
     *
     * @param array $all The output array containing all values
     * @return array The sanitized output array
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
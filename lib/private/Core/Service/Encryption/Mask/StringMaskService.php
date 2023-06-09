<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Core\Service\Encryption\Mask;

use KSP\Core\Service\Encryption\IStringMaskService;

class StringMaskService implements IStringMaskService {

    public function mask(string $plain): string {
        $length = strlen($plain);
        if ($length <= 4) {
            return str_pad('', $length, 'x');
        }
        $result    = '';
        $maxLength = $length - 4;
        if ($maxLength > 7) {
            $maxLength = 7;
        }
        $start  = substr($plain, 0, 2);
        $end    = substr($plain, -2);
        $padded = str_pad($result, $maxLength, 'x');
        return "$start$padded$end";
    }

}
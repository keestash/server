<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Service\Encryption;

use KSP\Core\Service\Encryption\IBase64Service;

class Base64Service implements IBase64Service {

    public function encrypt(string $value): string {
        return base64_encode($value);
    }

    public function decrypt(string $encrypted): string {
        return base64_decode($encrypted);
    }

    public function encryptArrayRecursive(array $array): array {
        foreach ($array as $key => $value) {
            if (true === is_array($value)) {
                $array[$key] = $this->encryptArrayRecursive($value);
                continue;
            }
            $array[$key] = base64_encode($value);
        }
        return $array;
    }

    public function decryptArrayRecursive(array $array): array {
        foreach ($array as $key => $value) {
            if (true === is_array($value)) {
                $array[$key] = $this->decryptArrayRecursive($value);
                continue;
            }
            $array[$key] = base64_decode($value);
        }
        return $array;
    }

}
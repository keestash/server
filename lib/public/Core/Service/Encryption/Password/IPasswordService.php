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

namespace KSP\Core\Service\Encryption\Password;

use Keestash\Core\DTO\Encryption\Password\Password;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Encryption\Password\IPassword;
use KSP\Core\Service\IService;

interface IPasswordService extends IService {

    public const KEY_SPECIAL_CHARACTERS = "characters.special";
    public const KEY_DIGIT              = "digit";
    public const KEY_UPPER_CASE         = "case.upper";
    public const KEY_LOWER_CASE         = "case.lower";

    public const LOWER_CASE_CHARACTERS = "abcdefghijklmnopqrstuvwxyz";
    public const SPECIAL_CHARACTERS    = "!@#$%^&*()_-=+;:,.?";
    public const UPPER_CASE_CHARACTERS = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    public const DIGITS                = "0123456789";

    /**
     * @param string $password
     * @return array
     */
    public function findCharacterSet(string $password): array;

    /**
     * @param int  $length
     * @param bool $hasUpperCase
     * @param bool $hasLowerCase
     * @param bool $hasDigits
     * @param bool $hasSpecialChars
     * @return Password
     * @throws KeestashException
     *
     * TODO pay credit to https://stackoverflow.com/questions/1837432/how-to-generate-random-password-with-php
     */
    public function generatePassword(int $length, bool $hasUpperCase, bool $hasLowerCase, bool $hasDigits, bool $hasSpecialChars): Password;

    public function measureQuality(IPassword $password): IPassword;

}
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

namespace Keestash\Core\Service\Encryption\Password;


use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\Encryption\Password\Password;
use Keestash\Exception\KeestashException;

class PasswordService {

    private const UPPER_CASE_KEY         = "case.upper";
    private const UPPER_CASE_CHARACTERS  = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    private const LOWER_CASE_KEY         = "case.lower";
    private const LOWER_CASE_CHARACTERS  = "abcdefghijklmnopqrstuvwxyz";
    private const DIGIT_KEY              = "digit";
    private const DIGITS                 = "0123456789";
    private const SPECIAL_CHARACTERS_KEY = "characters.special";
    private const SPECIAL_CHARACTERS     = "!@#$%^&*()_-=+;:,.?";

    private HashTable $characterTable;

    public function __construct() {
        $this->characterTable = new HashTable();
        $this->initCharTable();
    }

    private function initCharTable(): void {
        $this->characterTable->put(
            PasswordService::UPPER_CASE_KEY
            , PasswordService::UPPER_CASE_CHARACTERS
        );
        $this->characterTable->put(
            PasswordService::LOWER_CASE_KEY
            , PasswordService::LOWER_CASE_CHARACTERS
        );
        $this->characterTable->put(
            PasswordService::DIGIT_KEY
            , PasswordService::DIGITS
        );
        $this->characterTable->put(
            PasswordService::SPECIAL_CHARACTERS_KEY
            , PasswordService::SPECIAL_CHARACTERS
        );

    }

    private function getCharacterSet(string $key): string {
        if (false === $this->characterTable->containsKey($key)) {
            throw new KeestashException("no character set found for $key");
        }
        return (string) $this->characterTable->get($key);
    }

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
    public function generatePassword(int $length, bool $hasUpperCase, bool $hasLowerCase, bool $hasDigits, bool $hasSpecialChars): Password {
        $password           = new Password();
        $possibleCharacters = "";

        if (true === $hasUpperCase) {
            $set                = $this->getCharacterSet(PasswordService::UPPER_CASE_KEY);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        // if no options are set, we return a password with only lower case
        if (true === $hasLowerCase ||
            (
                false === $hasUpperCase &&
                false === $hasLowerCase &&
                false === $hasDigits &&
                false === $hasSpecialChars
            )) {
            $set                = $this->getCharacterSet(PasswordService::LOWER_CASE_KEY);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        if (true === $hasDigits) {
            $set                = $this->getCharacterSet(PasswordService::DIGIT_KEY);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        if (true === $hasSpecialChars) {
            $set                = $this->getCharacterSet(PasswordService::SPECIAL_CHARACTERS_KEY);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        $result = "";
        $i      = 0;
        while ($i < $length) {
            $char   = substr($possibleCharacters, mt_rand(0, strlen($possibleCharacters) - 1), 1);
            $result .= $char;
            $i++;
        }

        $password->setValue($result);
        $entropy = $this->getPasswordEntropy($password);
        $password->setEntropy($entropy);
        $password->setQuality(
            $this->getQuality($entropy)
        );

        return $password;

    }

    private function getQuality(float $entropy): int {
        $entropy = floor($entropy);

        if ($entropy < 100) {
            return -1;
        }

        if ($entropy < 250) {
            return 0;
        }

        return 1;
    }

    private function getPasswordEntropy(Password $password): float {
        $characterSet      = $password->getCharacterSet();
        $alphabet          = implode("", $characterSet);
        $length            = $password->getLength();
        $alphabetLength    = strlen($alphabet);
        $possiblePasswords = pow($alphabetLength, $length);

        return log($possiblePasswords) / log(2);
    }

}

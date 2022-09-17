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

use doganoo\DI\Object\String\IStringService;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\Encryption\Password\Password;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Encryption\Password\IPassword;
use KSP\Core\Service\Encryption\Password\IPasswordService;

class PasswordService implements IPasswordService {

    private HashTable      $characterTable;
    private IStringService $stringService;

    public function __construct(IStringService $stringService) {
        $this->characterTable = new HashTable();
        $this->stringService  = $stringService;
        $this->initCharTable();
    }

    private function initCharTable(): void {
        $this->characterTable->put(
            IPasswordService::KEY_UPPER_CASE
            , IPasswordService::UPPER_CASE_CHARACTERS
        );
        $this->characterTable->put(
            IPasswordService::KEY_LOWER_CASE
            , IPasswordService::LOWER_CASE_CHARACTERS
        );
        $this->characterTable->put(
            IPasswordService::KEY_DIGIT
            , IPasswordService::DIGITS
        );
        $this->characterTable->put(
            IPasswordService::KEY_SPECIAL_CHARACTERS
            , IPasswordService::SPECIAL_CHARACTERS
        );

    }

    public function findCharacterSet(string $password): array {
        $characterSet = [];
        if (strlen($this->stringService->intersect($password, IPasswordService::DIGITS)) > 0) {
            $characterSet[] = IPasswordService::DIGITS;
        }
        if (strlen($this->stringService->intersect($password, IPasswordService::SPECIAL_CHARACTERS)) > 0) {
            $characterSet[] = IPasswordService::SPECIAL_CHARACTERS;
        }
        if (strlen($this->stringService->intersect($password, IPasswordService::LOWER_CASE_CHARACTERS)) > 0) {
            $characterSet[] = IPasswordService::LOWER_CASE_CHARACTERS;
        }
        if (strlen($this->stringService->intersect($password, IPasswordService::UPPER_CASE_CHARACTERS)) > 0) {
            $characterSet[] = IPasswordService::UPPER_CASE_CHARACTERS;
        }
        return $characterSet;
    }

    public function getCharacterSet(string $key): string {
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
            $set                = $this->getCharacterSet(IPasswordService::KEY_UPPER_CASE);
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
            $set                = $this->getCharacterSet(IPasswordService::KEY_LOWER_CASE);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        if (true === $hasDigits) {
            $set                = $this->getCharacterSet(IPasswordService::KEY_DIGIT);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        if (true === $hasSpecialChars) {
            $set                = $this->getCharacterSet(IPasswordService::KEY_SPECIAL_CHARACTERS);
            $possibleCharacters = $possibleCharacters . $set;
            $password->addCharacterSet($set);
        }

        $result = "";
        $i      = 0;
        while ($i < $length) {
            $char   = substr($possibleCharacters, random_int(0, strlen($possibleCharacters) - 1), 1);
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

    private function getPasswordEntropy(IPassword $password): float {
        $characterSet      = $password->getCharacterSet();
        $alphabet          = implode("", $characterSet);
        $length            = $password->getLength();
        $alphabetLength    = strlen($alphabet);
        $possiblePasswords = pow($alphabetLength, $length);

        return log($possiblePasswords) / log(2);
    }

    public function measureQuality(IPassword $password): IPassword {
        $entropy = $this->getPasswordEntropy($password);
        $password->setEntropy($entropy);
        $password->setQuality(
            $this->getQuality($entropy)
        );
        return $password;
    }

}

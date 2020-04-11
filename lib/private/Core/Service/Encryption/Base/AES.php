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

namespace Keestash\Core\Encryption;

use doganoo\PHPUtil\Log\FileLogger;
use KSP\Core\Encryption\ICredential;
use KSP\Core\Encryption\IEncryption;

// TODO move to service
class AES implements IEncryption {

    public const METHOD         = "AES-256-CBC";
    public const HASH_ALGORITHM = "sha256";
    public const IV_LENGTH      = 16;

    protected $credential = null;

    public function __construct(ICredential $credential) {
        $this->credential = $credential;
    }

    public function encrypt($raw) {
        $key = hash(
            AES::HASH_ALGORITHM
            , $this->credential->getSecret()
            , true
        );

        $iv = openssl_random_pseudo_bytes(AES::IV_LENGTH);

        $cipherText = openssl_encrypt(
            $raw
            , AES::METHOD
            , $key
            , OPENSSL_RAW_DATA
            , $iv
        );

        $hash = hash_hmac(
            AES::HASH_ALGORITHM
            , $cipherText
            , $key
            , true
        );

        $encrypted = $iv . $hash . $cipherText;
        return $encrypted;
    }

    public function decrypt($encrypted) {
        $iv = substr(
            $encrypted
            , 0
            , AES::IV_LENGTH
        );

        $hash = substr(
            $encrypted
            , AES::IV_LENGTH
            , 32
        );

        $cipherText = substr($encrypted, 48);

        $key = hash(
            AES::HASH_ALGORITHM
            , $this->credential->getSecret()
            , true
        );

        $newHash = hash_hmac(
            AES::HASH_ALGORITHM
            , $cipherText
            , $key
            , true
        );

        if ($newHash !== $hash) {
            FileLogger::error("hashes do not match. There was an error. Aborting encryption");
            return null;
        }

        $decrypted = openssl_decrypt(
            $cipherText
            , AES::METHOD
            , $key
            , OPENSSL_RAW_DATA
            , $iv
        );

        return $decrypted;
    }


}
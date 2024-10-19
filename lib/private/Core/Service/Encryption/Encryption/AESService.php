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

namespace Keestash\Core\Service\Encryption\Encryption;

use Keestash\Exception\EncryptionFailedException;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\Service\Encryption\IEncryptionService;
use Psr\Log\LoggerInterface;

/**
 * AES Encryption service
 *
 * @package Keestash\Core\Service\Encryption\Base
 */
abstract class AESService implements IEncryptionService {

    public const string METHOD         = "AES-256-CBC";
    public const string HASH_ALGORITHM = "sha256";
    public const int    IV_LENGTH      = 16;

    public function __construct(private readonly LoggerInterface $logger) {
    }

    /**
     * @param ICredential $credential
     * @param string      $raw
     * @return string
     */
    #[\Override]
    public function encrypt(ICredential $credential, string $raw): string {
        $key = hash(
            AESService::HASH_ALGORITHM
            , $credential->getSecret()
            , true
        );

        $iv = openssl_random_pseudo_bytes(AESService::IV_LENGTH);

        $cipherText = openssl_encrypt(
            $raw
            , AESService::METHOD
            , (string) $key
            , OPENSSL_RAW_DATA
            , (string) $iv
        );

        $hash = hash_hmac(
            AESService::HASH_ALGORITHM
            , (string) $cipherText
            , (string) $key
            , true
        );

        return $iv . $hash . $cipherText;
    }

    /**
     * @param ICredential $credential
     * @param string      $encrypted
     * @return string
     * @throws EncryptionFailedException
     */
    #[\Override]
    public function decrypt(ICredential $credential, string $encrypted): string {
        $iv = substr(
            $encrypted
            , 0
            , AESService::IV_LENGTH
        );

        $hash = substr(
            $encrypted
            , AESService::IV_LENGTH
            , 32
        );

        $cipherText = substr($encrypted, 48);

        $key = hash(
            AESService::HASH_ALGORITHM
            , $credential->getSecret()
            , true
        );

        $newHash = hash_hmac(
            AESService::HASH_ALGORITHM
            , $cipherText
            , $key
            , true
        );

        if (!hash_equals($newHash, $hash)) {
            $this->logger->error("hashes do not match. There was an error. Aborting encryption");
            throw new EncryptionFailedException("hashes do not match. There was an error. Aborting encryption");
        }

        $decrypted = openssl_decrypt(
            $cipherText
            , AESService::METHOD
            , $key
            , OPENSSL_RAW_DATA
            , $iv
        );

        if (false === $decrypted) {
            throw new EncryptionFailedException();
        }

        return $decrypted;

    }


}

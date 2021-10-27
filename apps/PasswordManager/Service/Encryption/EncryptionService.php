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

namespace KSA\PasswordManager\Service\Encryption;

use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Encryption\AESService;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Encryption\IEncryptionService;

/**
 * Wrapper class for the core encryption service.
 * We just extend the core encryption service in order
 * to have an service within the PasswordManager service
 *
 * @package KSA\PasswordManager\Service\Encryption
 */
class EncryptionService extends AESService {

    private IEncryptionService $encryptionService;
    private CredentialService  $credentialService;

    public function __construct(
        IEncryptionService $encryptionService
        , CredentialService $credentialService
        , ILogger $logger
    ) {
        $this->encryptionService = $encryptionService;
        $this->credentialService = $credentialService;

        parent::__construct($logger);
    }

    public function encrypt(ICredential $credential, string $raw): string {
        return parent::encrypt(
            $this->prepareKey($credential)
            , $raw
        );
    }

    private function prepareKey(ICredential $credential): IKey {
        $tempKey = new Key();
        $tempKey->setId(
            $credential->getId()
        );
        $tempKey->setCreateTs(
            $credential->getCreateTs()
        );

        $keyHolderCredential = $this->credentialService->createCredential($credential->getKeyHolder());

        $tempKey->setSecret(
            $this->encryptionService->decrypt(
                $keyHolderCredential
                , $credential->getSecret()
            )
        );
        return $tempKey;
    }

    public function decrypt(ICredential $credential, string $encrypted): string {
        return parent::decrypt(
            $this->prepareKey($credential)
            , $encrypted
        );
    }

}

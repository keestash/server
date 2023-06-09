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

namespace KSA\PasswordManager\Service\Node\Credential\AdditionalData;

use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\AdditionalData;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\Value;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSP\Core\Service\Encryption\Key\IKeyService;

class EncryptionService {

    public function __construct(
        private readonly \KSA\PasswordManager\Service\Encryption\EncryptionService $encryptionService
        , private readonly IKeyService                                             $keyService
    ) {
    }

    public function encrypt(AdditionalData $additionalData, Credential $credential): AdditionalData {

        $keyHolder = $credential->getUser();
        if (null !== $credential->getOrganization()) {
            $keyHolder = $credential->getOrganization();
        }

        $encrypted = $this->encryptionService->encrypt(
            $this->keyService->getKey($keyHolder)
            , $additionalData->getValue()->getPlain()
        );

        return new AdditionalData(
            $additionalData->getId()
            , $additionalData->getKey()
            , new Value(
                plain: $additionalData->getValue()->getPlain()
                , encrypted: $encrypted
            )
            , $additionalData->getNodeId()
            , $additionalData->getCreateTs()
        );
    }

    public function decrypt(AdditionalData $data, Credential $credential): AdditionalData {
        $keyHolder = $credential->getUser();
        if (null !== $credential->getOrganization()) {
            $keyHolder = $credential->getOrganization();
        }

        $decrypted = $this->encryptionService->decrypt(
            $this->keyService->getKey($keyHolder)
            , $data->getValue()->getEncrypted()
        );

        return new AdditionalData(
            $data->getId()
            , $data->getKey()
            , new Value(
                plain: $decrypted
                , encrypted: $data->getValue()->getEncrypted()
            )
            , $data->getNodeId()
            , $data->getCreateTs()
        );
    }

}
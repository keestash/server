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

namespace Keestash\Core\Service\Encryption;

use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Credential\DerivedCredentialService;
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use Psr\Log\LoggerInterface;

class RecryptService {

    public function __construct(
        private readonly DerivedCredentialService $derivedCredentialService
        , private readonly CredentialService      $credentialService
        , private readonly IUserRepository        $userRepository
        , private readonly IUserKeyRepository     $encryptionRepository
        , private readonly IEncryptionService     $encryptionService
        , private readonly LoggerInterface        $logger
    ) {

    }

    public function recrypt(IUser $user): void {
        $oldCredential = $this->credentialService->createCredential($user);
        $newCredential = $this->derivedCredentialService->createCredential($user);
        $key           = $this->encryptionRepository->getKey($user);

        try {
            $oldSecretPlain = $this->encryptionService->decrypt($oldCredential, $key->getSecret());
        } catch (EncryptionFailedException $e) {
            $this->logger->error('can not decrypt key with old credential. Probably done in the past', ['oldcredential' => $oldCredential, 'user' => $user]);
            return;
        }

        $newSecret = $this->encryptionService->encrypt($newCredential, $oldSecretPlain);
        $key->setSecret($newSecret);
        $added = $this->encryptionRepository->updateKey($key);

        if (false === $added) {
            throw new KeestashException();
        }

    }

}
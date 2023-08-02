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

namespace KSA\PasswordManager\Event\Listener;

use DateTimeImmutable;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSA\PasswordManager\Exception\KeyNotUpdatedException;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class AfterPasswordChanged
 *
 * @package KSA\PasswordManager\Hook
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class AfterPasswordChanged implements IListener {

    public function __construct(
        private readonly IUserKeyRepository      $encryptionKeyRepository
        , private readonly IEncryptionService    $encryptionService
        , private readonly ICredentialService    $credentialService
        , private readonly LoggerInterface       $logger
        , private readonly IDerivationRepository $derivationRepository
        , private readonly IDerivationService    $derivationService
    ) {
    }

    /**
     * @param UserUpdatedEvent $event
     * @throws KeyNotFoundException
     * @throws KeyNotUpdatedException
     */
    public function execute(IEvent $event): void {
        $this->logger->debug('start AfterPasswordChange');
        if ($event->getUpdatedUser()->getPassword() === $event->getUser()->getPassword()) {
            $this->logger->debug('the passwords are the same - not updating!!');
            return;
        }

        $credential        = $this->credentialService->createCredentialFromDerivation($event->getUser());
        $updatedCredential = $this->credentialService->createCredentialFromDerivation($event->getUpdatedUser());
        $this->logger->debug('retrieved both, old and new credential');
        /** @var IKey|Key $key */
        $key = $this->encryptionKeyRepository->getKey($event->getUser());
        $this->logger->debug('retrieved key');

        $oldSecretPlain = $this->encryptionService->decrypt($credential, $key->getSecret());
        $newSecret = $this->encryptionService->encrypt($updatedCredential, $oldSecretPlain);
        $this->logger->debug('recrypted :)');

        $key->setSecret($newSecret);
        $added = $this->encryptionKeyRepository->updateKey($key);

        $this->derivationRepository->clear($event->getUpdatedUser());

        $this->derivationRepository->add(
            new Derivation(
                Uuid::uuid4()->toString()
                , $event->getUpdatedUser()
                , $this->derivationService->derive($event->getUpdatedUser()->getPassword())
                , new DateTimeImmutable()
            )
        );

        $this->logger->debug('updated :)');

        if (false === $added) {
            throw new KeyNotUpdatedException("key file is not updated!!");
        }
        $this->logger->debug('end AfterPasswordChange');
    }

}

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

use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSA\PasswordManager\Exception\KeyNotUpdatedException;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class AfterPasswordChanged
 *
 * @package KSA\PasswordManager\Hook
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class AfterPasswordChanged implements IListener {

    private IUserKeyRepository $encryptionRepository;
    private IEncryptionService $encryptionService;
    private CredentialService        $credentialService;
    private ILogger                  $logger;

    public function __construct(
        IUserKeyRepository $encryptionKeyRepository
        , IEncryptionService $encryptionService
        , CredentialService $credentialService
        , ILogger $logger
    ) {
        $this->encryptionRepository = $encryptionKeyRepository;
        $this->encryptionService    = $encryptionService;
        $this->credentialService    = $credentialService;
        $this->logger               = $logger;
    }

    /**
     * @param UserUpdatedEvent $event
     * @throws KeyNotFoundException
     * @throws KeyNotUpdatedException
     */
    public function execute(Event $event): void {

        if ($event->getUpdatedUser()->getPassword() === $event->getOldUser()->getPassword()) return;

        $currentCredential = $this->credentialService->getCredential($event->getUpdatedUser());
        $oldCredential     = $this->credentialService->getCredential($event->getOldUser());

        /** @var IKey|Key|null $key */
        $key = $this->encryptionRepository->getKey($event->getUpdatedUser());

        if (null === $key) {
            throw new KeyNotFoundException("no key found :(");
        }

        $oldSecretPlain = $this->encryptionService->decrypt($oldCredential, $key->getSecret());
        $newSecret      = $this->encryptionService->encrypt($currentCredential, $oldSecretPlain);

        $key->setSecret($newSecret);
        $added = $this->encryptionRepository->updateKey($key);

        if (false === $added) {
            throw new KeyNotUpdatedException("key file is not updated!!");
        }
    }

}

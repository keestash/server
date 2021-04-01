<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\GeneralApi\Event\Listener;

use DateTime;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use KSA\GeneralApi\Event\Organization\UserChangedEvent;
use KSA\GeneralApi\Repository\IOrganizationUserRepository;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IListener;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use Symfony\Contracts\EventDispatcher\Event;

class UserChangedListener implements IListener {

    private IOrganizationUserRepository $organizationUserRepository;
    private IOrganizationKeyRepository  $organizationKeyRepository;
    private IEncryptionService          $encryptionService;
    private CredentialService           $credentialService;
    private ILogger                     $logger;

    public function __construct(
        IOrganizationUserRepository $organizationUserRepository
        , IOrganizationKeyRepository $organizationKeyRepository
        , IEncryptionService $encryptionService
        , CredentialService $credentialService
        , ILogger $logger
    ) {
        $this->organizationUserRepository = $organizationUserRepository;
        $this->organizationKeyRepository  = $organizationKeyRepository;
        $this->encryptionService          = $encryptionService;
        $this->credentialService          = $credentialService;
        $this->logger                     = $logger;
    }

    /**
     * @param Event|UserChangedEvent $event
     */
    public function execute(Event $event): void {

        $organization = $this->organizationUserRepository->getByOrganization($event->getOrganization());

        $secret = null;

        /** @var IUser $user */
        foreach ($organization->getUsers() as $user) {
            $secret = $secret . $user->getPassword();
        }

        $key        = $this->organizationKeyRepository->getKey($organization);
        $credential = $this->credentialService->getCredential($organization);
        $secret     = $this->encryptionService->encrypt($credential, $secret);

        if (null === $key) {
            $this->insertKey($organization, $secret);
            return;
        }

        $this->updateKey($key, $secret);


    }

    private function updateKey(IKey $key, string $secret): void {
        $key->setSecret($secret);
        $this->organizationKeyRepository->updateKey($key);
    }

    private function insertKey(IOrganization $organization, string $secret): void {
        $key = new Key();
        $key->setCreateTs(new DateTime());
        $key->setSecret($secret);
        $inserted = $this->organizationKeyRepository->storeKey($organization, $key);
    }

}
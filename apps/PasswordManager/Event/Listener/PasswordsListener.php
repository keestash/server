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

namespace KSA\PasswordManager\Event\Listener;

use DateTimeImmutable;
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use KSA\PasswordManager\Api\Node\Pwned\ChangeState;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Pwned\Api\Passwords;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\Node\PwnedService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSA\Settings\Exception\SettingNotFoundException;
use KSA\Settings\Repository\IUserSettingRepository;
use KSP\Core\DTO\Event\IEvent;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Event\Listener\IListener;
use Psr\Log\LoggerInterface;

class PasswordsListener implements IListener {

    public function __construct(
        private readonly PwnedService               $pwnedService
        , private readonly PwnedPasswordsRepository $pwnedPasswordsRepository
        , private readonly NodeRepository           $nodeRepository
        , private readonly NodeEncryptionService    $nodeEncryptionService
        , private readonly LoggerInterface          $logger
        , private readonly IUserSettingRepository   $userSettingRepository
    ) {
    }

    #[\Override]
    public function execute(IEvent $event): void {
        return;
        $candidates = $this->pwnedPasswordsRepository->getOlderThan(
            (new DateTimeImmutable())->modify('-30 min')
        );

        /** @var \KSA\PasswordManager\Entity\Node\Pwned\Passwords $candidate */
        foreach ($candidates as $candidate) {
            if (false === $this->isActiveForUser($candidate->getNode()->getUser())) {
                $this->logger->debug('password check is deactivated for user. Skipping');
                continue;
            }
            try {
                $this->logger->debug(sprintf('processing %s', $candidate->getNode()->getId()));
                $credential = $this->nodeRepository->getNode($candidate->getNode()->getId());

                if (false === ($credential instanceof Credential)) {
                    continue;
                }

                $this->nodeEncryptionService->decryptNode($credential);

                $plainPassword = $credential->getPassword()->getPlain();

                $searchHash = $this->pwnedService->generateSearchHash($plainPassword);
                $this->logger->debug(sprintf('Search Hash %s', $searchHash));
                $passwordTree = $this->pwnedService->importPasswords($searchHash);

                $this->nodeEncryptionService->decryptNode($credential);

                $passwordsNode = $passwordTree->search(
                    new Passwords(
                        strtoupper(substr(
                            sha1((string) $plainPassword)
                            , 0
                            , 5
                        ))
                        , strtoupper(substr(
                            sha1((string) $plainPassword)
                            , 5
                        ))
                        , 0
                    )
                );

                if (null !== $passwordsNode) {
                    $this->logger->info('password leak found');
                }

                $this->pwnedPasswordsRepository->replace(
                    new \KSA\PasswordManager\Entity\Node\Pwned\Passwords(
                        $this->nodeRepository->getNode($candidate->getNode()->getId(), 0, 0)
                        , null !== $passwordsNode
                        ? (int) floor($passwordsNode->getValue()->getCount() % 10)
                        : 0
                        , $candidate->getCreateTs()
                        , new DateTimeImmutable()
                    )
                );

                sleep(10);
            } catch (EncryptionFailedException|DerivationException $e) {
                $this->logger->warning(
                    'passwords import failed'
                    , [
                        'exception' => $e
                    ]
                );
            }
        }
    }

    private function isActiveForUser(IUser $user): bool {
        $setting = null;
        try {
            $setting = $this->userSettingRepository->get(ChangeState::USER_SETTING_PWNED_ACTIVE, $user);
        } catch (SettingNotFoundException $e) {
            $this->logger->debug('no setting found', ['exception' => $e]);
            $setting = null;
        }
        return null !== $setting;
    }

}
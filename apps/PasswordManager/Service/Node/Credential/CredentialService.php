<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Service\Node\Credential;

use DateTime;
use DateTimeImmutable;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Credential\Password\Entropy;
use KSA\PasswordManager\Entity\Node\Credential\Password\Password;
use KSA\PasswordManager\Entity\Node\Credential\Password\URL;
use KSA\PasswordManager\Entity\Node\Credential\Password\Username;
use KSA\PasswordManager\Entity\Node\Node as NodeObject;
use KSA\PasswordManager\Event\Node\Credential\CredentialCreatedEvent;
use KSA\PasswordManager\Event\Node\Credential\CredentialUpdatedEvent;
use KSA\PasswordManager\Exception\Node\NodeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\Event\IEventService;
use Psr\Log\LoggerInterface;

class CredentialService {

    private EdgeService           $edgeService;
    private NodeRepository        $nodeRepository;
    private NodeEncryptionService $nodeEncryptionService;
    private IPasswordService      $passwordService;
    private IEventService         $eventManager;

    public function __construct(
        EdgeService                        $edgeService
        , NodeRepository                   $nodeRepository
        , NodeEncryptionService            $nodeEncryptionService
        , IPasswordService                 $passwordService
        , IEventService                    $eventManager
        , private readonly LoggerInterface $logger
    ) {
        $this->edgeService           = $edgeService;
        $this->nodeRepository        = $nodeRepository;
        $this->nodeEncryptionService = $nodeEncryptionService;
        $this->passwordService       = $passwordService;
        $this->eventManager          = $eventManager;
    }

    public function createCredential(
        string   $password
        , string $url
        , string $userName
        , string $title
        , IUser  $user
    ): Credential {

        $credential = new Credential();
        $credential->setCreateTs(new DateTime());
        $credential->setUpdateTs(null);
        $credential->setType(NodeObject::CREDENTIAL);

        $urlObject = new URL();
        $urlObject->setPlain($url);
        $credential->setUrl($urlObject);

        $usernameObject = new Username();
        $usernameObject->setPlain($userName);
        $credential->setUsername($usernameObject);

        $p = new Password();
        $p->setPlain($password);
        $credential->setPassword($p);
        $credential->setName($title);
        $credential->setUser($user);

        $corePassword = new \Keestash\Core\DTO\Encryption\Password\Password();
        $corePassword->setValue((string) $p->getPlain());
        $corePassword->setCharacterSet(
            $this->passwordService->findCharacterSet((string) $p->getPlain())
        );
        $corePassword = $this->passwordService->measureQuality($corePassword);
        $entropy      = new Entropy();
        $entropy->setPlain((string) $corePassword->getEntropy());
        $credential->setEntropy($entropy);
        return $credential;
    }

    /**
     * @param Credential $credential
     * @param Folder     $parent
     * @return Edge
     * @throws PasswordManagerException
     * @throws NodeException
     */
    public function insertCredential(Credential $credential, Folder $parent): Edge {
        $this->nodeEncryptionService->encryptNode($credential);
        $credential = $this->nodeRepository->addCredential($credential);
        $edge       = $this->nodeRepository->addEdge(
            $this->edgeService->prepareRegularEdge($credential, $parent)
        );
        $this->eventManager->execute(
            new CredentialCreatedEvent($credential)
        );
        return $edge;
    }

    public function updateCredential(
        Credential $credential
        , string   $userName
        , string   $url
        , string   $name
    ): Credential {
        $this->nodeEncryptionService->decryptNode($credential);

        $usernameObject = new Username();
        $usernameObject->setPlain($userName);
        $credential->setUsername($usernameObject);

        $urlObject = new URL();
        $urlObject->setPlain($url);
        $credential->setUrl($urlObject);

        $credential->setName($name);
        $credential->setUpdateTs(new DateTime());

        $this->nodeEncryptionService->encryptNode($credential);
        $credential = $this->nodeRepository->updateCredential($credential);
        $this->eventManager->execute(
            new CredentialUpdatedEvent(
                $credential
                , new DateTimeImmutable()
            )
        );
        return $credential;
    }

    public function updatePassword(
        Credential $credential
        , string   $password
    ): Credential {
        $passwordObject = $credential->getPassword();
        $passwordObject->setPlain($password);
        $credential->setPassword($passwordObject);
        $credential->setUpdateTs(new DateTime());

        $corePassword = new \Keestash\Core\DTO\Encryption\Password\Password();
        $corePassword->setValue((string) $credential->getPassword()->getPlain());
        $corePassword->setCharacterSet(
            $this->passwordService->findCharacterSet((string) $credential->getPassword()->getPlain())
        );

        $corePassword = $this->passwordService->measureQuality($corePassword);
        $entropy      = new Entropy();
        $entropy->setPlain((string) $corePassword->getEntropy());
        $credential->setEntropy($entropy);

        $this->nodeEncryptionService->encryptNode($credential);
        $credential = $this->nodeRepository->updateCredential($credential);
        $this->eventManager->execute(
            new CredentialUpdatedEvent(
                $credential
                , new DateTimeImmutable()
            )
        );
        $this->logger->debug('updated password');
        return $credential;
    }

    public function getDecryptedPassword(Credential $credential): string {
        $this->nodeEncryptionService->decryptNode($credential);
        return $credential->getPassword()->getPlain();
    }

    public function getDecryptedUsername(Credential $credential): string {
        $this->nodeEncryptionService->decryptNode($credential);
        return $credential->getUsername()->getPlain();
    }

    public function removeCredential(Credential $credential): void {
        $this->nodeRepository->remove($credential);
    }

}

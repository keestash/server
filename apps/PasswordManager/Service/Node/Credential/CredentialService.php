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
use Keestash\Exception\EncryptionFailedException;
use Keestash\Exception\Repository\Derivation\DerivationException;
use Keestash\Exception\User\UserException;
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
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KSP\Core\Service\Event\IEventService;
use Psr\Log\LoggerInterface;

final readonly class CredentialService {

    public function __construct(
        private EdgeService      $edgeService,
        private NodeRepository   $nodeRepository,
        private IPasswordService $passwordService,
        private IEventService    $eventManager,
        private LoggerInterface  $logger
    ) {
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
        $urlObject->setEncrypted($url);
        $credential->setUrl($urlObject);

        $usernameObject = new Username();
        $usernameObject->setEncrypted($userName);
        $credential->setUsername($usernameObject);

        $p = new Password();
        $p->setEncrypted($password);
        $credential->setPassword($p);
        $credential->setName($title);
        $credential->setUser($user);

        $corePassword = new \Keestash\Core\DTO\Encryption\Password\Password();
        $corePassword->setValue($p->getEncrypted());
        $corePassword->setCharacterSet(
            $this->passwordService->findCharacterSet((string) $p->getEncrypted()) //todo fixme
        );
        $corePassword = $this->passwordService->measureQuality($corePassword);
        $entropy      = new Entropy();
        $entropy->setEncrypted((string) $corePassword->getEntropy());
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
        $credential = $this->nodeRepository->addCredential($credential);
        $edge       = $this->nodeRepository->addEdge(
            $this->edgeService->prepareRegularEdge($credential, $parent)
        );
        $this->eventManager->execute(
            new CredentialCreatedEvent($credential)
        );
        return $edge;
    }

    /**
     * @param Credential $credential
     * @param string     $userName
     * @param string     $url
     * @param string     $name
     * @return Credential
     * @throws EncryptionFailedException
     * @throws DerivationException
     * @throws UserException
     */
    public function updateCredential(
        Credential $credential
        , string   $userName
        , string   $url
        , string   $name
    ): Credential {
        $usernameObject = new Username();
        $usernameObject->setEncrypted($userName);
        $credential->setUsername($usernameObject);

        $urlObject = new URL();
        $urlObject->setEncrypted($url);
        $credential->setUrl($urlObject);

        $credential->setName($name);
        $credential->setUpdateTs(new DateTime());

        $credential = $this->nodeRepository->updateCredential($credential);
        $this->eventManager->execute(
            new CredentialUpdatedEvent(
                $credential
                , new DateTimeImmutable()
            )
        );
        return $credential;
    }

    /**
     * @param Credential $credential
     * @param string     $password
     * @return Credential
     */
    public function updatePassword(
        Credential $credential
        , string   $password
    ): Credential {
        $passwordObject = $credential->getPassword();
        $passwordObject->setEncrypted($password);
        $credential->setPassword($passwordObject);
        $credential->setUpdateTs(new DateTime());

        $corePassword = new \Keestash\Core\DTO\Encryption\Password\Password();
        $corePassword->setValue((string) $credential->getPassword()->getEncrypted());
        $corePassword->setCharacterSet(
            $this->passwordService->findCharacterSet((string) $credential->getPassword()->getEncrypted()) // todo fixme
        );

        $corePassword = $this->passwordService->measureQuality($corePassword);
        $entropy      = new Entropy();
        $entropy->setEncrypted((string) $corePassword->getEntropy());
        $credential->setEntropy($entropy);

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

    public function removeCredential(Credential $credential): void {
        $this->nodeRepository->remove($credential);
    }

}

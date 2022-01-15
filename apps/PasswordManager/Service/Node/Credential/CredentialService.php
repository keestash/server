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
use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node as NodeObject;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Entity\Password\Password;
use KSA\PasswordManager\Entity\Password\URL;
use KSA\PasswordManager\Entity\Password\Username;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSP\Core\DTO\User\IUser;

class CredentialService {

    private EncryptionService     $encryptionService;
    private KeyService            $keyService;
    private EdgeService           $edgeService;
    private NodeRepository        $nodeRepository;
    private NodeService           $nodeService;
    private NodeEncryptionService $nodeEncryptionService;

    public function __construct(
        EncryptionService       $encryptionService
        , KeyService            $keyService
        , EdgeService           $edgeService
        , NodeRepository        $nodeRepository
        , NodeService           $nodeService
        , NodeEncryptionService $nodeEncryptionService
    ) {
        $this->encryptionService     = $encryptionService;
        $this->keyService            = $keyService;
        $this->edgeService           = $edgeService;
        $this->nodeRepository        = $nodeRepository;
        $this->nodeService           = $nodeService;
        $this->nodeEncryptionService = $nodeEncryptionService;
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

        return $credential;
    }

    public function insertCredential(Credential $credential, Folder $parent): Edge {
        $this->nodeEncryptionService->encryptNode($credential);
        $credential = $this->nodeRepository->addCredential($credential);
        return $this->nodeRepository->addEdge(
            $this->edgeService->prepareRegularEdge($credential, $parent)
        );
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
        $this->nodeEncryptionService->encryptNode($credential);
        return $this->nodeRepository->updateCredential($credential);
    }

    public function updatePassword(
        Credential $credential
        , string   $password
    ): Credential {
        $passwordObject = $credential->getPassword();
        $passwordObject->setPlain($password);
        $credential->setPassword($passwordObject);
        $this->nodeEncryptionService->encryptNode($credential);
        return $this->nodeRepository->updateCredential($credential);
    }

    public function getDecryptedPassword(Credential $credential): string {
        $this->nodeEncryptionService->decryptNode($credential);
        return $credential->getPassword()->getPlain();
    }

}

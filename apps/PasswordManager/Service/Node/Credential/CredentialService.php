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
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Core\DTO\User\IUser;

class CredentialService {

    private EncryptionService $encryptionService;
    private KeyService        $keyService;
    private EdgeService       $edgeService;
    private NodeRepository    $nodeRepository;
    private NodeService       $nodeService;

    public function __construct(
        EncryptionService $encryptionService
        , KeyService $keyService
        , EdgeService $edgeService
        , NodeRepository $nodeRepository
        , NodeService $nodeService
    ) {
        $this->encryptionService = $encryptionService;
        $this->keyService        = $keyService;
        $this->edgeService       = $edgeService;
        $this->nodeRepository    = $nodeRepository;
        $this->nodeService       = $nodeService;
    }

    public function createCredential(
        string $password
        , string $url
        , string $userName
        , string $title
        , IUser $user
        , Folder $parent
        , string $notes = ""
    ): Credential {
        $organization = $this->nodeService->getOrganization($parent);
        $keyHolder    = null !== $organization ? $organization : $user;

        $p = new Password();
        $p->setPlain($password);
        $p->setEncrypted(
            $this->encryptionService->encrypt(
                $this->keyService->getKey($keyHolder)
                , $password
            )
        );
        $p->setLength(strlen($password));
        $p->setPlaceholder(
            $this->generatePasswordPlaceholder()
        );

        $credential = new Credential();
        $credential->setCreateTs(new DateTime());
        $credential->setType(NodeObject::CREDENTIAL);
        $credential->setUrl(
            $this->encryptionService->encrypt(
                $this->keyService->getKey($keyHolder)
                , $url
            )
        );
        $credential->setUsername(
            $this->encryptionService->encrypt(
                $this->keyService->getKey($keyHolder)
                , $userName
            )
        );
        $credential->setNotes(
            $this->encryptionService->encrypt(
                $this->keyService->getKey($keyHolder)
                , $notes
            )
        );
        $credential->setPassword($p);
        $credential->setName($title);
        $credential->setUser($user);

        return $credential;
    }

    public function generatePasswordPlaceholder(?string $password = null): string {

        $length = 12;
        if (null !== $password) {
            $length = strlen($password);
        }
        return str_pad('', $length, "*");
    }

    public function insertCredential(Credential $credential, Folder $parent): Edge {
        $credential = $this->nodeRepository->addCredential($credential);
        return $this->nodeRepository->addEdge(
            $this->edgeService->prepareRegularEdge($credential, $parent)
        );
    }

    public function updateCredential(
        Credential $credential
        , string $userName
        , string $url
        , string $name
    ): Credential {
        $credential->setUsername(
            $userName
        );
        $credential->setUrl(
            $url
        );

        $credential->setName($name);
        return $this->nodeRepository->updateCredential($credential);
    }

    public function updatePassword(
        Credential $credential
        , string $password
    ): Credential {
        $organization = $this->nodeService->getOrganization($credential);
        $keyHolder    = null !== $organization ? $organization : $credential->getUser();
        $key          = $this->keyService->getKey($keyHolder);

        $passwordObject = $credential->getPassword();
        $passwordObject->setEncrypted(
            $this->encryptionService->encrypt(
                $key
                , $password
            )
        );
        $passwordObject->setPlain($password);
        $credential->setPassword($passwordObject);
        return $this->nodeRepository->updateCredential($credential);
    }

    public function getDecryptedPassword(Credential $credential): string {
        $organization = $this->nodeService->getOrganization($credential);
        $keyHolder    = null !== $organization ? $organization : $credential->getUser();
        $key          = $this->keyService->getKey($keyHolder);

        return $this->encryptionService->decrypt(
            $key
            , $credential->getPassword()->getEncrypted()
        );
    }

}

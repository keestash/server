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

namespace KSA\PasswordManager\Test\Unit\Service\Node\Credential;

use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use KSA\PasswordManager\Test\Unit\TestCase;
use KSP\Core\DTO\User\IUser;
use Ramsey\Uuid\Uuid;

class CredentialServiceTest extends TestCase {

    private CredentialService     $credentialService;
    private EncryptionService     $encryptionService;
    private KeyService            $keyService;
    private NodeEncryptionService $nodeEncryptionService;
    private NodeRepository        $nodeRepository;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->credentialService     = $this->getServiceManager()->get(CredentialService::class);
        $this->encryptionService     = $this->getServiceManager()->get(EncryptionService::class);
        $this->keyService            = $this->getServiceManager()->get(KeyService::class);
        $this->nodeRepository        = $this->getServiceManager()->get(NodeRepository::class);
        $this->nodeEncryptionService = $this->getServiceManager()->get(NodeEncryptionService::class);
    }

    private function getCredential(IUser $user, bool $encrypt = false): Credential {
        $password = "mySuperSecurePassword";
        $url      = "keestash.com";
        $userName = "keestashSystemUser";
        $title    = "organization.keestash.com";

        $credential = $this->credentialService->createCredential(
            $password
            , $url
            , $userName
            , $title
            , $user
        );

        if (true === $encrypt) {
            $this->nodeEncryptionService->encryptNode($credential);
        }
        return $credential;
    }

    public function testCreateCredential(): void {
        $password = "mySuperSecurePassword";
        $url      = "keestash.com";
        $userName = "keestashSystemUser";
        $title    = "organization.keestash.com";

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $credential = $this->getCredential($user, true);
        $key        = $this->keyService->getKey($user);

        $this->assertTrue($credential instanceof Credential);
        $this->assertTrue($credential instanceof Node);
        $this->assertTrue($credential->getPassword()->getPlain() === $password);
        $this->assertTrue($credential->getPassword()->getLength() === strlen($password));
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , (string) $credential->getPassword()->getEncrypted()
            ) === $password
        );
        /**
         * @see \KSA\PasswordManager\Service\Node\Credential\CredentialService::generatePasswordPlaceholder()
         */
        $this->assertTrue($credential->getPassword()->getPlaceholder() === "************");
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , (string) $credential->getUrl()->getEncrypted()
            ) === $url);
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , (string) $credential->getUsername()->getEncrypted()
            ) === $userName);
        $this->assertTrue($credential->getName() === $title);
        $this->assertTrue($credential->getUser()->getId() === $user->getId());
        $this->assertTrue($credential->getType() === Node::CREDENTIAL);
        $this->removeUser($user);
    }

    public function testInsertCredential(): void {
        $user       = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $credential = $this->getCredential($user);
        $root       = $this->nodeRepository->getRootForUser($user);
        $edge       = $this->credentialService->insertCredential($credential, $root);
        $this->assertTrue($edge instanceof Edge);
        $this->assertIsInt($edge->getNode()->getId()); // indicates that the DB AI PK is set
        $this->removeUser($user);
    }

    public function testUpdatePassword(): void {
        $user        = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $credential  = $this->getCredential($user);
        $edge        = $this->credentialService->insertCredential(
            $credential,
            $this->nodeRepository->getRootForUser($user)
        );
        $newPassword = "myNewPassword";

        $this->assertIsInt($edge->getNode()->getId());

        $credential = $this->credentialService->updatePassword($credential, $newPassword);

        $this->assertTrue($credential->getPassword()->getPlain() === $newPassword);
    }

}
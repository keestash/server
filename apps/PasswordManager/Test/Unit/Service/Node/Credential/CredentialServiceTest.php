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
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KST\TestCase;

class CredentialServiceTest extends TestCase {

    private CredentialService $credentialService;
    private EncryptionService $encryptionService;
    private KeyService        $keyService;
    private NodeRepository    $nodeRepository;

    protected function setUp(): void {
        parent::setUp();
        $this->credentialService = $this->getServiceManager()->get(CredentialService::class);
        $this->encryptionService = $this->getServiceManager()->get(EncryptionService::class);
        $this->keyService        = $this->getServiceManager()->get(KeyService::class);
        $this->nodeRepository    = $this->getServiceManager()->get(NodeRepository::class);
    }

    private function getCredential(): Credential {
        $password = "mySuperSecurePassword";
        $url      = "keestash.com";
        $userName = "keestashSystemUser";
        $title    = "organization.keestash.com";
        $parent   = new Folder();
        $note     = "this is a test note";

        return $this->credentialService->createCredential(
            $password
            , $url
            , $userName
            , $title
            , $this->getUser()
            , $parent
            , $note
        );
    }

    public function testCreateCredential(): void {
        $password = "mySuperSecurePassword";
        $url      = "keestash.com";
        $userName = "keestashSystemUser";
        $title    = "organization.keestash.com";
        $note     = "this is a test note";

        $credential = $this->getCredential();
        $key        = $this->keyService->getKey($this->getUser());

        $this->assertTrue($credential instanceof Credential);
        $this->assertTrue($credential instanceof Node);
        $this->assertTrue($credential->getPassword()->getPlain() === $password);
        $this->assertTrue($credential->getPassword()->getLength() === strlen($password));
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , $credential->getPassword()->getEncrypted()
            ) === $password
        );
        /**
         * @see \KSA\PasswordManager\Service\Node\Credential\CredentialService::generatePasswordPlaceholder()
         */
        $this->assertTrue($credential->getPassword()->getPlaceholder() === "************");
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , (string) $credential->getUrl()
            ) === $url);
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , $credential->getUsername()
            ) === $userName);
        $this->assertTrue($credential->getName() === $title);
        $this->assertTrue($credential->getUser()->getId() === $this->getUser()->getId());
        $this->assertTrue(
            $this->encryptionService->decrypt(
                $key
                , (string) $credential->getNotes()
            ) === $note);
        $this->assertTrue($credential->getType() === Node::CREDENTIAL);
    }

    public function testPasswordPlaceholder(): void {
        $placeHolder = $this->credentialService->generatePasswordPlaceholder();
        $this->assertTrue(strlen($placeHolder) === 12);
        $this->assertTrue($placeHolder === "************");
    }

    public function testInsertCredential(): void {
        $credential = $this->getCredential();
        $root       = $this->nodeRepository->getRootForUser($this->getUser());
        $edge       = $this->credentialService->insertCredential($credential, $root);
        $this->assertTrue($edge instanceof Edge);
        $this->assertIsInt($edge->getNode()->getId()); // indicates that the DB AI PK is set
    }

    public function testGetDecryptedPassword(): void {
        $credential = $this->getCredential();
        $decrypted  = $this->credentialService->getDecryptedPassword($credential);
        $this->assertTrue($decrypted === $credential->getPassword()->getPlain());
    }

    public function testUpdatePassword(): void {
        $credential  = $this->getCredential();
        $edge        = $this->credentialService->insertCredential($credential, $this->nodeRepository->getRootForUser($this->getUser()));
        $newPassword = "myNewPassword";

        $this->assertIsInt($edge->getNode()->getId());

        $credential = $this->credentialService->updatePassword($credential, $newPassword);

        $this->assertTrue($credential->getPassword()->getPlain() === $newPassword);
    }

}
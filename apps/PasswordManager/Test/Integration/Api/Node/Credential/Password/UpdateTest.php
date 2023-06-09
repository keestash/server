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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Credential\Password;

use Keestash\Exception\EncryptionFailedException;
use KSA\PasswordManager\Api\Node\Credential\Password\Update;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class UpdateTest extends TestCase {

    /**
     * @dataProvider getNonExistentData
     */
    public function testNonExistent(?string $passwordPlain, ?int $nodeId): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Update $update */
        $update   = $this->getServiceManager()->get(Update::class);
        $response = $update->handle(
            $this->getVirtualRequest(
                [
                    'passwordPlain' => $passwordPlain
                    , 'nodeId'      => $nodeId
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testUpdateOnNonCredential(): void {
        $this->expectException(PasswordManagerException::class);
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root = $this->getRootFolder($user);
        /** @var Update $update */
        $update   = $this->getServiceManager()->get(Update::class);
        $response = $update->handle(
            $this->getVirtualRequest(
                [
                    'passwordPlain' => uniqid()
                    , 'nodeId'      => $root->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testUpdateCredential(): void {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        $user           = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root           = $this->getRootFolder($user);

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $root
        );

        /** @var Update $update */
        $update      = $this->getServiceManager()->get(Update::class);
        $newPassword = uniqid();
        $response    = $update->handle(
            $this->getVirtualRequest(
                [
                    'passwordPlain' => $newPassword
                    , 'nodeId'      => $edge->getNode()->getId()
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));

        /** @var Credential $retrievedCredential */
        $retrievedCredential = $nodeRepository->getNode($edge->getNode()->getId(), 0, 0);

        $this->assertInstanceOf(Credential::class, $retrievedCredential);
        $this->assertSame($edge->getNode()->getId(), $retrievedCredential->getId());
        $this->assertSame($newPassword, $credentialService->getDecryptedPassword($retrievedCredential));
        $this->removeUser($user);
    }

    public function getNonExistentData(): array {
        return [
            ['passwordPlain' => null, 'nodeId' => null]
            , ['passwordPlain' => 'dsfsdfdsfdasdsa', 'nodeId' => null]
            , ['passwordPlain' => 'sfsdfsdfdsfsdf', 'nodeId' => 9999]
            , ['passwordPlain' => null, 'nodeId' => 9999]
        ];
    }

}
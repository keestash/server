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

use KSA\PasswordManager\Api\Node\Credential\Password\Update;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\TestCase;

class UpdateTest extends TestCase {

    public function getNonExistentData(): array {
        return [
            ['passwordPlain' => null, 'nodeId' => null]
            , ['passwordPlain' => 'dsfsdfdsfdasdsa', 'nodeId' => null]
            , ['passwordPlain' => 'sfsdfsdfdsfsdf', 'nodeId' => 9999]
            , ['passwordPlain' => null, 'nodeId' => 9999]
        ];
    }

    /**
     * @dataProvider getNonExistentData
     */
    public function testNonExistent(?string $passwordPlain, ?int $nodeId): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Update $update */
        $update   = $this->getServiceManager()->get(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest(
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
        $root = $this->getRootForUser();
        /** @var Update $update */
        $update   = $this->getServiceManager()->get(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest(
                [
                    'passwordPlain' => uniqid()
                    , 'nodeId'      => $root->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testUpdateCredential(): void {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        $credential        = $this->createCredential(
            "updateTestPassword"
            , "keestash.test"
            , "updateTest"
            , "UpdateTestPassword"
        );
        /** @var Update $update */
        $update      = $this->getServiceManager()->get(Update::class);
        $newPassword = uniqid();
        $response    = $update->handle(
            $this->getDefaultRequest(
                [
                    'passwordPlain' => $newPassword
                    , 'nodeId'      => $credential->getId()
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));

        /** @var Credential $retrievedCredential */
        $retrievedCredential = $this->getNode($credential->getId());

        $this->assertInstanceOf(Credential::class, $retrievedCredential);
        $this->assertSame($credential->getId(), $retrievedCredential->getId());
        $this->assertSame($newPassword, $credentialService->getDecryptedPassword($retrievedCredential));

    }

}
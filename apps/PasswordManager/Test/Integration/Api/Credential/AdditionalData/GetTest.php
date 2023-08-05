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

namespace KSA\PasswordManager\Test\Integration\Api\Credential\AdditionalData;

use DateTimeImmutable;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\AdditionalData;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\Value;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Service\Node\Credential\AdditionalData\EncryptionService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class GetTest extends TestCase {

    public function testRegularCase(): void {
        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->getService(EncryptionService::class);
        /** @var AdditionalDataRepository $additionalDataRepository */
        $additionalDataRepository = $this->getService(AdditionalDataRepository::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $edge  = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );
        $key   = Uuid::uuid4()->toString();
        $value = Uuid::uuid4()->toString();

        $additionalData = new AdditionalData(
            Uuid::uuid4()->toString()
            , $key
            , new Value(
                plain: $value,
                encrypted: null
            )
            , $edge->getNode()->getId()
            , new DateTimeImmutable()
        );

        $additionalData = $encryptionService->encrypt($additionalData, $edge->getNode());
        $additionalDataRepository->add($additionalData);

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , str_replace(':credentialId', (string) $edge->getNode()->getId(), ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET)
                    , []
                    , $user
                    , $headers
                )
            );

        $data = json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('data', $data);
        $this->assertCount(1, $data['data']);
        $this->assertArrayHasKey('id', $data['data'][0]);
        $this->assertTrue($data['data'][0]['id'] === $additionalData->getId());
        $this->assertArrayHasKey('key', $data['data'][0]);
        $this->assertTrue($data['data'][0]['key'] === $additionalData->getKey());
        $this->assertArrayHasKey('nodeId', $data['data'][0]);
        $this->assertTrue($data['data'][0]['nodeId'] === $additionalData->getNodeId());
        $this->assertArrayHasKey('createTs', $data['data'][0]);
        $this->assertTrue(strtotime($data['data'][0]['createTs']['date']) === $additionalData->getCreateTs()->getTimestamp());
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testForbidden(): void {
        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->getService(EncryptionService::class);
        /** @var AdditionalDataRepository $additionalDataRepository */
        $additionalDataRepository = $this->getService(AdditionalDataRepository::class);

        $password  = Uuid::uuid4()->toString();
        $user      = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $otherUser = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $edge  = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $otherUser
            , $this->getRootFolder($otherUser)
        );
        $key   = Uuid::uuid4()->toString();
        $value = Uuid::uuid4()->toString();

        $additionalData = new AdditionalData(
            Uuid::uuid4()->toString()
            , $key
            , new Value(
                plain: $value,
                encrypted: null
            )
            , $edge->getNode()->getId()
            , new DateTimeImmutable()
        );

        $additionalData = $encryptionService->encrypt($additionalData, $edge->getNode());
        $additionalDataRepository->add($additionalData);

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , str_replace(':credentialId', (string) $edge->getNode()->getId(), ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET)
                    , []
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::FORBIDDEN, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
        $this->removeUser($otherUser);
    }

    public function testNonExisting(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , str_replace(':credentialId', Uuid::uuid4()->toString(), ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_ADDITIONAL_DATA_GET)
                    , []
                    , $user
                    , $headers
                )
            );
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }


}
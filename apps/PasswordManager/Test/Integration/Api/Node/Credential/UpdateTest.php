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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Credential;

use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

/**
 * Class UpdateTest
 * @package KSA\PasswordManager\Test\Integration\Api\Node\Credential
 * @author  Dogan Ucar <dogan.ucar@check24.de>
 * TODO test non existent parameters once the API handles them
 */
class UpdateTest extends TestCase {

    public function testUpdate(): void {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        $password          = Uuid::uuid4()->toString();
        $user              = $this->createUser(
            Uuid::uuid4()->toString(),
            $password
        );
        $root              = $this->getRootFolder($user);
        $node              = $credentialService->createCredential(
            "deleteTestPassword"
            , "keestash.test"
            , "deletetest.test"
            , "Deletetest"
            , $user
        );
        $edge              = $credentialService->insertCredential($node, $root);
        $node              = $edge->getNode();

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE
                    , [
                        'name'       => 'TestUpdateNewName'
                        , 'username' => base64_encode('TestUpdateNewUsername')
                        , 'url'      => base64_encode('TestUpdateNewUrl')
                        , 'nodeId'   => $node->getId()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
        $this->logout($headers, $user);
    }

    public function testUpdateInvalidNodeId(): void {
        $this->expectException(PasswordManagerException::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE
                    , [
                        'name'       => 'TestUpdateNewName'
                        , 'username' => 'TestUpdateNewUsername'
                        , 'url'      => 'TestUpdateNewUrl'
                        , 'nodeId'   => 9999
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->logout($headers, $user);
        $this->removeUser($user);

    }

    public function testUpdateNoNodeId(): void {
        $this->expectException(PasswordManagerException::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers = $this->login($user, $password);

        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_UPDATE
                    , [
                        'name'       => 'TestUpdateNewName'
                        , 'username' => 'TestUpdateNewUsername'
                        , 'url'      => 'TestUpdateNewUrl'
                    ]
                    , $user
                    , $headers
                )
            );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->logout($headers, $user);
        $this->removeUser($user);

    }

}

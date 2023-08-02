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

use KSA\PasswordManager\Api\Node\Credential\Create;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class CreateTest extends TestCase {

    public function testInvalid(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithoutMandatatory(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'username'   => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithoutIncompleteMandatory(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'name'       => 'CreateCredentialTestName'
                    , 'username' => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithoutIncompleteMandatorySecond(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'parent'     => 'root'
                    , 'username' => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testValid(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString(),
            $password
        );
        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_CREDENTIAL_CREATE
                    , [
                        'parent'     => 'root'
                        , 'name'     => 'CreateCredentialTestName'
                        , 'username' => 'CreateCredentialTest'
                        , 'password' => 'MyAwesomeSuperSecurePassword'
                        , 'note'     => 'This password is super secure'
                        , 'url'      => 'keestash.test'
                    ]
                    , $user
                    , $headers
                )
            );
        $this->assertStatusCode(IResponse::OK, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testWithCredentialAsParent(): void {
        /** @var Create $create */
        $create = $this->getServiceManager()->get(Create::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
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

        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'parent'     => (string) $edge->getNode()->getId()
                    , 'name'     => 'CreateCredentialTestName'
                    , 'username' => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

}
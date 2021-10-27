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
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KST\TestCase;

class CreateTest extends TestCase {

    public function testInvalid(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithoutMandatatory(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(
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
            $this->getDefaultRequest(
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
            $this->getDefaultRequest(
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
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent'     => 'root'
                    , 'name'     => 'CreateCredentialTestName'
                    , 'username' => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithCredentialAsParent(): void {
        /** @var Create $create */
        $create = $this->getServiceManager()->get(Create::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $root           = $nodeRepository->getRootForUser($user);
        $node           = $credentialService->createCredential(
            "deleteTestPassword"
            , "keestash.test"
            , "deletetest.test"
            , "Deletetest"
            , $user
        );
        $edge           = $credentialService->insertCredential($node, $root);
        $node           = $edge->getNode();
        $response       = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent'     => $node->getId()
                    , 'name'     => 'CreateCredentialTestName'
                    , 'username' => 'CreateCredentialTest'
                    , 'password' => 'MyAwesomeSuperSecurePassword'
                    , 'note'     => 'This password is super secure'
                    , 'url'      => 'keestash.test'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

}
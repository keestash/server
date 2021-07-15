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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Credential\Password;

use Keestash\Core\DTO\Token\Token;
use KSA\PasswordManager\Api\Node\Credential\Password\Get;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;
use KST\TestCase;

class GetTest extends TestCase {

    public function testWithNoNodeId(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle($this->getDefaultRequest());
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithInvalidNodeId(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle($this->getDefaultRequest()->withAttribute('id', 9999));
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testInvalidOwner(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getServiceManager()->get(IUserRepository::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $root           = $nodeRepository->getRootForUser($user);
        $node           = $credentialService->createCredential(
            "getPasswordTest"
            , "keestash.test"
            , "getpassword.test"
            , "GetPasswordTest"
            , $user
            , $root
        );
        $edge           = $credentialService->insertCredential($node, $root);
        $node           = $edge->getNode();

        $request = $this->getDefaultRequest();
        $request = $request->withAttribute('id', $node->getId());
        /** @var IToken|Token $token */
        $token = $request->getAttribute(IToken::class);
        /** @phpstan-ignore-next-line */
        $token->setUser($userRepository->getUserById((string) UserService::TEST_USER_ID_3));
        $request  = $request->withAttribute(IToken::class, $token);
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testGetNonPassword(): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $root           = $nodeRepository->getRootForUser($user);

        $request  = $this->getDefaultRequest();
        $request  = $request->withAttribute('id', $root->getId());
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testGet(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $user           = $this->getUser();
        $root           = $nodeRepository->getRootForUser($user);
        $node           = $credentialService->createCredential(
            "getPasswordTest"
            , "keestash.test"
            , "getpassword.test"
            , "GetPasswordTest"
            , $user
            , $root
        );
        $edge           = $credentialService->insertCredential($node, $root);
        $node           = $edge->getNode();

        $request  = $this->getDefaultRequest();
        $request  = $request->withAttribute('id', $node->getId());
        $response = $get->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
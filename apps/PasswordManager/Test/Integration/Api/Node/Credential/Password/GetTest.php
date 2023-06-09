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
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class GetTest extends TestCase {

    public function testWithNoNodeId(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle($this->getVirtualRequest());
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithInvalidNodeId(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle($this->getVirtualRequest()->withAttribute('id', 9999));
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testInvalidOwner(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getServiceManager()->get(IUserRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root = $this->getRootFolder($user);
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $root
        );

        $request = $this->getVirtualRequest();
        $request = $request->withAttribute('id', $edge->getNode()->getId());
        /** @var IToken|Token $token */
        $token = $request->getAttribute(IToken::class);
        /** @phpstan-ignore-next-line */
        $token->setUser($userRepository->getUserById((string) UserService::TEST_USER_ID_3));
        $request  = $request->withAttribute(IToken::class, $token);
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testGetNonPassword(): void {
        /** @var Get $get */
        $get  = $this->getServiceManager()->get(Get::class);
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root = $this->getRootFolder($user);

        $request  = $this->getVirtualRequest();
        $request  = $request->withAttribute('id', $root->getId());
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testGet(): void {
        /** @var Get $get */
        $get  = $this->getServiceManager()->get(Get::class);
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root = $this->getRootFolder($user);
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $root
        );
        $node = $edge->getNode();

        $request  = $this->getVirtualRequest();
        $request  = $request->withAttribute('node_id', $node->getId());
        $response = $get->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
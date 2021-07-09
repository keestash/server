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

namespace KSA\PasswordManager\Test\Integration\Api\Node;

use KSA\PasswordManager\Api\Node\Get;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;
use KST\TestCase;

class GetTest extends TestCase {

    public function testGet(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $node           = $nodeRepository->getRootForUser($this->getUser());

        $this->assertTrue($node instanceof Node);
        $this->assertTrue($node instanceof Root);

        $request  = $this->getDefaultRequest();
        $response = $get->handle($request->withAttribute('id', $node->getId()));
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testGetNonExisting(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
        );
    }

    public function testNonExistingWithId(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);

        $request  = $this->getDefaultRequest();
        $response = $get->handle($request->withAttribute('id', 99999));
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testNotOwnedByUser(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getServiceManager()->get(IUserRepository::class);
        $node           = $nodeRepository->getRootForUser($this->getUser());

        $this->assertTrue($node instanceof Node);
        $this->assertTrue($node instanceof Root);

        $request = $this->getDefaultRequest();
        $request = $request->withAttribute('id', $node->getId());
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $token->setUser($userRepository->getUserById((string) UserService::TEST_USER_ID_3));
        $request  = $request->withAttribute(IToken::class, $token);
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

}
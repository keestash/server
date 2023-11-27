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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Get;

use KSA\PasswordManager\Api\Node\Get\Get;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IResponseService;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class GetTest extends TestCase {

    public function testGet(): void {
        /** @var Get $get */
        $get  = $this->getServiceManager()->get(Get::class);
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $root = $this->getRootFolder($user);

        $request  = $this->getVirtualRequest();
        $response = $get->handle(
            $request->withAttribute('node_id', (string) $root->getId())
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testGetNonExisting(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
        );
    }

    public function testNonExistingWithId(): void {
        /** @var Get $get */
        $get = $this->getServiceManager()->get(Get::class);

        $request  = $this->getVirtualRequest();
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
        $user           = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $node           = $nodeRepository->getRootForUser($user);

        $this->assertTrue($node instanceof Node);
        $this->assertTrue($node instanceof Root);

        $request = $this->getVirtualRequest();
        $request = $request->withAttribute('id', $node->getId());
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $token->setUser($userRepository->getUserById((string) UserService::TEST_USER_ID_3));
        $request  = $request->withAttribute(IToken::class, $token);
        $response = $get->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithInValidNodeId(): void {
        /** @var IResponseService $responseService */
        $responseService = $this->getService(IResponseService::class);
        $password        = Uuid::uuid4()->toString();
        $user            = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $headers         = $this->login($user, $password);
        $response        = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , str_replace(':node_id', Uuid::uuid4()->toString(), ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_ID)
                    , []
                    , $user
                    , $headers
                )
            );

        $data = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertTrue($data['responseCode'] === $responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_INVALID_NODE_ID));
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testGetRoot(): void {
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
                    , str_replace(':node_id', Node::ROOT, ConfigProvider::PASSWORD_MANAGER_NODE_GET_BY_ID)
                    , []
                    , $user
                    , $headers
                )
            );

        $data = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('breadCrumb', $data);
        $this->assertArrayHasKey('node', $data);
        $this->assertArrayHasKey('type', $data['node']);
        $this->assertArrayHasKey('user', $data['node']);
        $this->assertArrayHasKey('id', $data['node']['user']);
        $this->assertArrayHasKey('comments', $data);
        $this->assertArrayHasKey('pwned', $data);
        $this->assertTrue($data['node']['type'] === Node::ROOT);
        $this->assertTrue($data['node']['user']['id'] === $user->getId());
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

}
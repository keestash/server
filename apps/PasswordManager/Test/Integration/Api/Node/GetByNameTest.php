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

use KSA\PasswordManager\Api\Node\GetByName;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KST\TestCase;

class GetByNameTest extends TestCase {

    public function testGet(): void {
        /** @var GetByName $get */
        $get = $this->getServiceManager()->get(GetByName::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $node           = $nodeRepository->getRootForUser($this->getUser());

        $this->assertTrue($node instanceof Node);
        $this->assertTrue($node instanceof Root);

        $request  = $this->getDefaultRequest();
        $response = $get->handle($request->withAttribute('name', $node->getName()));
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testGetNonExisting(): void {
        /** @var GetByName $get */
        $get      = $this->getServiceManager()->get(GetByName::class);
        $response = $get->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
        );
    }

    public function testNonExistingWithName(): void {
        /** @var GetByName $get */
        $get = $this->getServiceManager()->get(GetByName::class);

        $request  = $this->getDefaultRequest();
        $response = $get->handle($request->withAttribute('name', md5(uniqid('', true))));
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $data = $this->getResponseService()->getResponseData($response);
        $this->assertIsArray($data['message']);
        $this->assertArrayHasKey('content', $data['message']);
        $this->assertCount(0, $data['message']['content']);
    }

}
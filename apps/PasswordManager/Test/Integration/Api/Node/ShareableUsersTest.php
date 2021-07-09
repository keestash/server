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

use KSA\PasswordManager\Api\Node\ShareableUsers;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KST\TestCase;

class ShareableUsersTest extends TestCase {

    public function testShareableUsers(): void {
        /** @var ShareableUsers $shareableUsers */
        $shareableUsers = $this->getServiceManager()->get(ShareableUsers::class);
        $user           = $this->getUser();
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        $node           = $nodeRepository->getNode(2, 0, 0);

        $this->assertInstanceOf(Credential::class, $node);
        $request  = $this->getDefaultRequest();
        $request  = $request->withAttribute('nodeId', $node->getId());
        $request  = $request->withAttribute('query', 'TestUser');
        $response = $shareableUsers->handle($request);

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
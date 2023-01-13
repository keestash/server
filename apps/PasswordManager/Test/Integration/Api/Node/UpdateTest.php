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

namespace KSA\PasswordManager\Test\Integration\Api\Node;

use KSA\PasswordManager\Api\Node\Update;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\TestCase;
use KSP\Api\IResponse;

class UpdateTest extends TestCase {

    public function testRegularCase(): void {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        /** @var Update $update */
        $update = $this->getService(Update::class);
        $user   = $this->getUser();
        $parent = $this->getRootForUser();
        $node   = $credentialService->createCredential(
            "deleteTestPassword"
            , "keestash.test"
            , "deletetest.test"
            , "Deletetest"
            , $user
        );
        $edge   = $credentialService->insertCredential($node, $parent);

        $response = $update->handle(
            $this->getDefaultRequest(
                [
                    'node_id' => $edge->getNode()->getId()
                    , 'name'  => UpdateTest::class
                ]
            )
        );

        $retrievedNode = $nodeRepository->getNode($edge->getNode()->getId(), 0, 0);
        $this->assertTrue(
            true === $this->getResponseService()->isValidResponse($response)
        );
        $this->assertTrue(
            UpdateTest::class === $retrievedNode->getName()
        );
        $nodeRepository->remove($retrievedNode);
    }

    public function handleEmptyRequest(): void {
        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
        );
        $this->assertTrue($response->getStatusCode() === IResponse::BAD_REQUEST);
    }

    public function handleWithUnknownNode(): void {
        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest(
                [
                    'node_id' => 34456454566453452345
                ]
            )
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
        );
        $this->assertTrue($response->getStatusCode() === IResponse::NOT_FOUND);
    }
}
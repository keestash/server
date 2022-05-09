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

use KSA\PasswordManager\Api\Node\Credential\Update;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KST\TestCase;

/**
 * Class UpdateTest
 * @package KSA\PasswordManager\Test\Integration\Api\Node\Credential
 * @author  Dogan Ucar <dogan.ucar@check24.de>
 * TODO test non existent parameters once the API handles them
 */
class UpdateTest extends TestCase {

    public function testUpdate(): void {
        /** @var Update $update */
        $update = $this->getServiceManager()->get(Update::class);
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

        $response = $update->handle(
            $this->getDefaultRequest([
                'name'       => 'TestUpdateNewName'
                , 'username' => [
                    "plain" => 'TestUpdateNewUsername'
                ]
                , 'url'      => [
                    "plain" => 'TestUpdateNewUrl'
                ]
                , 'nodeId'   => $node->getId()
            ])
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testUpdateInvalidNodeId(): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Update $update */
        $update = $this->getServiceManager()->get(Update::class);

        $response = $update->handle(
            $this->getDefaultRequest([
                'name'       => 'TestUpdateNewName'
                , 'username' => 'TestUpdateNewUsername'
                , 'url'      => 'TestUpdateNewUrl'
                , 'nodeId'   => 9999
            ])
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testUpdateNoNodeId(): void {
        $this->expectException(PasswordManagerException::class);
        /** @var Update $update */
        $update = $this->getServiceManager()->get(Update::class);

        $response = $update->handle(
            $this->getDefaultRequest([
                'name'       => 'TestUpdateNewName'
                , 'username' => 'TestUpdateNewUsername'
                , 'url'      => 'TestUpdateNewUrl'
            ])
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

}
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

use DateTime;
use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KST\TestCase;

class MoveTest extends TestCase {

    public function testMove(): void {
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);
        /** @var Move $move */
        $move = $this->getServiceManager()->get(Move::class);
        /** @var NodeService $nodeService */
        $nodeService = $this->getServiceManager()->get(NodeService::class);
        $user        = $this->getUser();
        $userRoot    = $nodeRepository->getRootForUser($user);

        $node        = $credentialService->createCredential(
            "moveTestPassword"
            , "keestash.test"
            , "move.test"
            , "MoveTst"
            , $user
            , $userRoot
        );
        $currentEdge = $credentialService->insertCredential($node, $userRoot);

        $this->assertTrue($userRoot->getId() === $currentEdge->getParent()->getId());

        $newFolder = new Folder();
        $newFolder->setUser($user);
        $newFolder->setCreateTs(new DateTime());
        $newFolder->setName('TheNewFolder');
        $newFolder->setType(Folder::FOLDER);

        $folderId = $nodeRepository->addFolder($newFolder);
        $newFolder->setId((int) $folderId);

        $newFolderEdge = $nodeService->prepareRegularEdge(
            $newFolder
            , $userRoot
            , $user
        );

        $newFolderEdge = $nodeRepository->addEdge($newFolderEdge);

        $node    = $currentEdge->getNode();
        $request = $this->getDefaultRequest(
            [
                'id'               => $node->getId()
                , 'parent_node_id' => $currentEdge->getParent()->getId()
                , 'target_node_id' => $newFolderEdge->getNode()->getId()
            ]
        );


        $response = $move->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithMissingData(): void {
        /** @var Move $move */
        $move = $this->getServiceManager()->get(Move::class);

        $data = [
            [
                'id'               => 1
                , 'parent_node_id' => 2
            ]
            , [
                'id' => 1
            ]
            , []
            , [
                'parent_node_id'   => 1
                , 'target_node_id' => 2
            ]
            , [
                'target_node_id' => 2
            ]
            , [
                'parent_node_id' => 1
            ]
            , [
                'id'               => 1
                , 'target_node_id' => 2
            ]
        ];

        foreach ($data as $datum) {
            $request  = $this->getDefaultRequest($datum);
            $response = $move->handle($request);
            $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        }

    }

}
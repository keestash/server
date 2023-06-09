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

use KSA\PasswordManager\Api\Node\Move;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class MoveTest extends TestCase {

    public function testMove(): void {
        /** @var Move $move */
        $move     = $this->getServiceManager()->get(Move::class);
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $userRoot = $this->getRootFolder($user);

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $userRoot
        );

        $this->assertTrue($userRoot->getId() === $edge->getParent()->getId());

        $newFolderEdge = $this->createAndInsertFolder(
            $user
            , Uuid::uuid4()->toString()
            , $userRoot
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_NODE_MOVE
                    , [
                    'node_id'          => $edge->getNode()->getId()
                    , 'target_node_id' => $newFolderEdge->getNode()->getId()
                ],
                    $user
                    , $headers
                )
            );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->logout($headers, $user);
        $this->removeUser($user);

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
            $request  = $this->getVirtualRequest($datum);
            $response = $move->handle($request);
            $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        }

    }

}
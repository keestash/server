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

namespace Integration\Api\Node\Share;

use KSA\PasswordManager\Api\Node\Share\Share;
use KSA\PasswordManager\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class ShareTest extends TestCase {

    public function testShare(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user      = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $otherUser = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge      = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );
        $node      = $edge->getNode();
        $response  = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $otherUser->getId()
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testShareWithoutNodeId(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'user_id_to_share' => $user->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testShareWithoutUserId(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id' => $edge->getNode()->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testNonShareableSameUser(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $user->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testNonShareableLockedUser(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user       = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $lockedUser = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , true
        );
        $edge       = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $lockedUser->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
        $this->removeUser($lockedUser);
    }

    public function testSharePreviouslyShared(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $user      = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $otherUser = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge      = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $otherUser->getId()
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));

        $response = $share->handle(
            $this->getVirtualRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $otherUser->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
        $this->removeUser($otherUser);
    }


}

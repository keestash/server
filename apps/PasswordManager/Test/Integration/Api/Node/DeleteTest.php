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

use KSA\PasswordManager\Api\Node\Delete;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class DeleteTest extends TestCase {

    public function testDelete(): void {
        /** @var Delete $delete */
        $delete = $this->getServiceManager()->get(Delete::class);
        $user   = $this->createUser(
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
        $node = $edge->getNode();

        $request = $this->getRequestService()
            ->getVirtualRequestWithToken(
                $user
                , []
                , []
                , ['node_id' => $node->getId()]
            );

        $response = $delete->handle($request);

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testDeleteNonDeletable(): void {
        /** @var Delete $delete */
        $delete  = $this->getServiceManager()->get(Delete::class);
        $user    = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $node    = $this->getRootFolder($user);
        $request = $this->getRequestService()
            ->getVirtualRequestWithToken(
                $user
                , []
                , []
                , ['id' => $node->getId()]
            );


        $response = $delete->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testDeleteNonExisting(): void {
        /** @var Delete $delete */
        $delete  = $this->getServiceManager()->get(Delete::class);
        $user    = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $request = $this->getRequestService()
            ->getVirtualRequestWithToken(
                $user
                , []
                , []
                , ['id' => 9999]
            );


        $response = $delete->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testDeleteNotOwnedByUser(): void {
        /** @var Delete $delete */
        $delete = $this->getServiceManager()->get(Delete::class);
        $user   = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getServiceManager()->get(IUserRepository::class);

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $request = $this->getRequestService()
            ->getVirtualRequestWithToken(
                $userRepository->getUserById((string) UserService::TEST_LOCKED_USER_ID_4)
                , []
                , []
                , ['id' => $edge->getNode()->getId()]
            );

        $response = $delete->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

}
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

namespace KSA\PasswordManager\Test\Integration\Api\Share;

use KSA\PasswordManager\Api\Node\Share\Remove;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSA\PasswordManager\Test\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class RemoveTest extends TestCase {

    public function testRemoveShare(): void {
        /** @var Remove $remove */
        $remove = $this->getServiceManager()->get(Remove::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getServiceManager()->get(PublicShareRepository::class);

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
        $node = $edge->getNode();

        $publicShare = $shareService->createPublicShare($node);
        $publicShare->setNodeId($node->getId());
        $node->setPublicShare($publicShare);
        $node = $shareRepository->shareNode($node);

        $request  = $this->getVirtualRequest(['shareId' => $node->getPublicShare()->getId()]);
        $response = $remove->handle($request);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->removeUser($user);
    }

    public function testRemoveShareWithoutId(): void {
        /** @var Remove $remove */
        $remove = $this->getServiceManager()->get(Remove::class);

        $request  = $this->getVirtualRequest();
        $response = $remove->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

}
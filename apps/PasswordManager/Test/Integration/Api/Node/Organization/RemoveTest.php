<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Organization;

use KSA\PasswordManager\Api\Node\Organization\Remove;
use KSA\PasswordManager\Repository\Node\OrganizationRepository as OrganizationNodeRepository;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\Settings\Repository\OrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use Ramsey\Uuid\Uuid;

class RemoveTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var Remove $remove */
        $remove   = $this->getService(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }

    public function testWithOrganizationNotFund(): void {
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

        /** @var Remove $remove */
        $remove   = $this->getService(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => 9999999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
        $this->removeUser($user);
    }

    public function testWithNonMatchingOrganization(): void {
        /** @var OrganizationNodeRepository $organizationNodeRepository */
        $organizationNodeRepository = $this->getService(OrganizationNodeRepository::class);

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

        $organization = $this->createAndInsertOrganization(Uuid::uuid4()->toString());
        $organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);

        /** @var Remove $remove */
        $remove   = $this->getService(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $organization->getId() + 1
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ALLOWED === $response->getStatusCode());
        $this->removeUser($user);
    }

    public function testRegularCase(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var OrganizationNodeRepository $organizationNodeRepository */
        $organizationNodeRepository = $this->getService(OrganizationNodeRepository::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(OrganizationRepository::class);

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

        $organization = $this->createAndInsertOrganization(Uuid::uuid4()->toString());

        $organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);
        /** @var Remove $remove */
        $remove   = $this->getService(Remove::class);
        $response = $remove->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $organization->getId()
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $organizationRepository->remove($organization);
        $this->removeUser($user);
    }

}
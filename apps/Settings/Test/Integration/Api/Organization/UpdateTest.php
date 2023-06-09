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

namespace KSA\Settings\Test\Integration\Api\Organization;

use DateTimeImmutable;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Api\Organization\Update;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;

class UpdateTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNonExisting(): void {
        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'id' => -1
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithExistingButNoData(): void {
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService  = $this->getService(IUserService::class);
        $organization = new Organization();
        $organization->setName(UpdateTest::class);
        $organization->setPassword(
            $userService->hashPassword(
                md5((string) time())
            )
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setCreateTs(new DateTimeImmutable());
        $organization = $organizationRepository->insert($organization);

        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'id' => $organization->getId()
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
        $organizationRepository->remove($organization);
    }

    public function testWithExistingButInvalidName(): void {
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService  = $this->getService(IUserService::class);
        $organization = new Organization();
        $organization->setName(UpdateTest::class);
        $organization->setPassword(
            $userService->hashPassword(
                md5((string) time())
            )
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setCreateTs(new DateTimeImmutable());
        $organization = $organizationRepository->insert($organization);

        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'id'     => $organization->getId()
                    , 'name' => ''
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService  = $this->getService(IUserService::class);
        $organization = new Organization();
        $organization->setName(UpdateTest::class);
        $organization->setPassword(
            $userService->hashPassword(
                md5((string) time())
            )
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setCreateTs(new DateTimeImmutable());
        $organization = $organizationRepository->insert($organization);

        $newName = 'updated-' . $organization->getName();
        /** @var Update $add */
        $add          = $this->getService(Update::class);
        $response     = $add->handle(
            $this->getVirtualRequest(
                [
                    'id'     => $organization->getId()
                    , 'name' => $newName
                    , 'active' => true
                ]
            )
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($newName === $responseBody['organization']['name']);
        $organizationRepository->remove($organization);
    }

}
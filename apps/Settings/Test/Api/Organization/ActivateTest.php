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

namespace KSA\Settings\Test\Api\Organization;

use DateTimeImmutable;
use DateTimeInterface;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Api\Organization\Activate;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use Ramsey\Uuid\Uuid;

class ActivateTest extends TestCase {

    public function testWithMissingParameters(): void {
        /** @var Activate $activate */
        $activate = $this->getService(Activate::class);
        $response = $activate->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithNotExistingOrganization(): void {
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService  = $this->getService(IUserService::class);
        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(null);
        $organization->setPassword(
            $userService->hashPassword((string) Uuid::uuid4())
        );
        $organization->setName(ActivateTest::class);

        $organization = $organizationRepository->insert($organization);
        /** @var Activate $activate */
        $activate     = $this->getService(Activate::class);
        $response     = $activate->handle(
            $this->getDefaultRequest(
                [
                    'id'         => $organization->getId()
                    , 'activate' => true
                ]
            )
        );
        $organization = $organizationRepository->get($organization->getId());
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($organization->getActiveTs() instanceof DateTimeInterface);
    }


}
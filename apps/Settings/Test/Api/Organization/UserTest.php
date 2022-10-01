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
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Api\Organization\User;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Nonstandard\Uuid;

class UserTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithOrganizationIdButNoUser(): void {
        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'organization_id' => 1
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithNotExistingOrganization(): void {
        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'organization_id' => 9999
                    , 'user_id'       => 9999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithExistingOrganizationButNotExistingUser(): void {
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
        $organization->setName(UserTest::class);
        $organization = $organizationRepository->insert($organization);

        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'organization_id' => $organization->getId()
                    , 'user_id'       => 99999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
        $organizationRepository->remove($organization);
    }

    public function testWithExistingOrganizationAndUserButNoMode(): void {
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
        $organization->setName(UserTest::class);
        $organization = $organizationRepository->insert($organization);

        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'organization_id' => $organization->getId()
                    , 'user_id'       => UserService::TEST_PASSWORD_RESET_USER_ID_5
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
        $organizationRepository->remove($organization);
    }

    /**
     * @param string $mode
     * @param int    $responseCode
     * @return void
     * @throws \KSA\Settings\Exception\SettingsException
     * @dataProvider getModes
     */
    public function testWithExistingOrganizationAndUserAndMode(string $mode, int $responseCode): void {
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
        $organization->setName(UserTest::class);
        $organization = $organizationRepository->insert($organization);

        /** @var User $add */
        $add      = $this->getService(User::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'organization_id' => $organization->getId()
                    , 'user_id'       => UserService::TEST_PASSWORD_RESET_USER_ID_5
                    , 'mode'          => $mode
                ]
            )
        );
        $this->assertTrue((IResponse::OK === $responseCode) === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue($responseCode === $response->getStatusCode());
        $organizationRepository->remove($organization);
    }

    public function getModes(): array {
        return [
            [User::MODE_ADD, IResponse::OK]
            , [User::MODE_ADD, IResponse::OK]
        ];
    }

}
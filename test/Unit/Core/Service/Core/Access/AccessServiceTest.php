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

namespace KST\Unit\Core\Service\Core\Access;

use DateTimeImmutable;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Repository\IOrganizationRepository;
use KSA\Settings\Repository\IOrganizationUserRepository;
use KSP\Core\DTO\Access\IAccessable;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Access\IAccessService;
use KSP\Core\Service\User\IUserService;
use KST\Service\Service\UserService;
use KST\Unit\TestCase;

class AccessServiceTest extends TestCase {

    public function testHasAccess(): void {
        /** @var IAccessService $accessService */
        $accessService = $this->getService(IAccessService::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);

        $accessable = new class($user) implements IAccessable {

            private IUser $user;

            public function __construct(IUser $user) {
                $this->user = $user;
            }

            public function getUser(): IUser {
                return $this->user;
            }

            public function getOrganization(): ?IOrganization {
                return null;
            }

        };
        $this->assertTrue(true === $accessService->hasAccess($accessable, $user));
    }

    public function testHasAccessWithOrganization(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IAccessService $accessService */
        $accessService = $this->getService(IAccessService::class);
        /** @var IOrganizationUserRepository $organizationUserRepository */
        $organizationUserRepository = $this->getService(IOrganizationUserRepository::class);

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setName(AccessServiceTest::class);
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );

        $organization = $organizationRepository->insert($organization);

        $user         = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);
        $organization = $organizationUserRepository->insert($user, $organization);

        $accessable = new class($user, $organization) implements IAccessable {

            private IUser         $user;
            private IOrganization $organization;

            public function __construct(IUser $user, IOrganization $organization) {
                $this->user         = $user;
                $this->organization = $organization;
            }

            public function getUser(): IUser {
                return $this->user;
            }

            public function getOrganization(): ?IOrganization {
                return $this->organization;
            }

        };

        $this->assertTrue(true === $accessService->hasAccess($accessable, $user));
    }

}
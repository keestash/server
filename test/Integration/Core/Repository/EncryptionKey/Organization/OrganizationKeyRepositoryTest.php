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

namespace KST\Integration\Core\Repository\EncryptionKey\Organization;

use DateTimeImmutable;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\DTO\Organization\Organization;
use KSA\Settings\Repository\IOrganizationRepository;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Service\User\IUserService;
use KST\Integration\TestCase;

class OrganizationKeyRepositoryTest extends TestCase {

    public function testStoreAndRemoveKey(): void {
        /** @var IOrganizationKeyRepository $organizationKeyRepository */
        $organizationKeyRepository = $this->getService(IOrganizationKeyRepository::class);
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setName(OrganizationKeyRepositoryTest::class);
        $organization = $organizationRepository->insert($organization);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($organization);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );
        $key = $organizationKeyRepository->storeKey($organization, $key);
        $this->assertInstanceOf(IKey::class, $key);
        $organization = $organizationRepository->remove($organization);
        $this->assertTrue($organization instanceof IOrganization);
        $this->assertTrue(
            true === $organizationKeyRepository->remove($organization)
        );
    }

    public function testStoreAndGetAndRemoveKey(): void {
        /** @var IOrganizationKeyRepository $organizationKeyRepository */
        $organizationKeyRepository = $this->getService(IOrganizationKeyRepository::class);
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setName(OrganizationKeyRepositoryTest::class);
        $organization = $organizationRepository->insert($organization);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($organization);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );
        $key = $organizationKeyRepository->storeKey($organization, $key);
        $this->assertInstanceOf(IKey::class, $key);
        $retrievedKey = $organizationKeyRepository->getKey($organization);
        $this->assertTrue($retrievedKey instanceof IKey);
        $organization = $organizationRepository->remove($organization);
        $this->assertTrue($organization instanceof IOrganization);
        $this->assertTrue(
            true === $organizationKeyRepository->remove($organization)
        );
    }

    public function testStoreAndGetAndUpdateAndRemoveKey(): void {
        /** @var IOrganizationKeyRepository $organizationKeyRepository */
        $organizationKeyRepository = $this->getService(IOrganizationKeyRepository::class);
        /** @var IOrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(IOrganizationRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setName(OrganizationKeyRepositoryTest::class);
        $organization = $organizationRepository->insert($organization);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($organization);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );
        $key = $organizationKeyRepository->storeKey($organization, $key);
        $this->assertInstanceOf(IKey::class, $key);
        $retrievedKey = $organizationKeyRepository->getKey($organization);
        $this->assertTrue($retrievedKey instanceof IKey);
        $retrievedKey->setCreateTs(new DateTimeImmutable());
        $this->assertTrue(true === $organizationKeyRepository->updateKey($retrievedKey));
        $organization = $organizationRepository->remove($organization);
        $this->assertTrue($organization instanceof IOrganization);
        $this->assertTrue(
            true === $organizationKeyRepository->remove($organization)
        );
    }

}
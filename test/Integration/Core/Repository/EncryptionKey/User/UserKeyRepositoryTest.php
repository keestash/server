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

namespace KST\Integration\Core\Repository\EncryptionKey\User;

use DateTimeImmutable;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KST\Service\Service\UserService;
use KST\TestCase;

class UserKeyRepositoryTest extends TestCase {

    public function testStoreAndRemoveKey(): void {
        /** @var IUserKeyRepository $userKeyRepository */
        $userKeyRepository = $this->getService(IUserKeyRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $user = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($user);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );

        $stored = $userKeyRepository->storeKey($user, $key);
        $this->assertTrue(true === $stored);
        $this->assertTrue(true === $userKeyRepository->remove($user));
    }

    public function testStoreAndGetAndRemoveKey(): void {
        /** @var IUserKeyRepository $userKeyRepository */
        $userKeyRepository = $this->getService(IUserKeyRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $user = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($user);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );

        $stored       = $userKeyRepository->storeKey($user, $key);
        $retrievedKey = $userKeyRepository->getKey($user);
        $this->assertTrue($retrievedKey instanceof IKey);
        $this->assertTrue(true === $stored);
        $this->assertTrue(true === $userKeyRepository->remove($user));
    }

    public function testStoreAndGetAndUpdateAndRemoveKey(): void {
        /** @var IUserKeyRepository $userKeyRepository */
        $userKeyRepository = $this->getService(IUserKeyRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $user = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);

        $key = new Key();
        $key->setCreateTs(new DateTimeImmutable());
        $key->setKeyHolder($user);
        $key->setSecret(
            $userService->hashPassword(md5((string) time()))
        );

        $stored = $userKeyRepository->storeKey($user, $key);
        $this->assertTrue(true === $stored);
        $retrievedKey = $userKeyRepository->getKey($user);
        $this->assertTrue($retrievedKey instanceof IKey);
        $retrievedKey->setSecret(
            $userService->hashPassword(md5((string) time()))
        );
        $this->assertTrue(
            true === $userKeyRepository->updateKey($retrievedKey)
        );
        $this->assertTrue(true === $userKeyRepository->remove($user));
    }

}
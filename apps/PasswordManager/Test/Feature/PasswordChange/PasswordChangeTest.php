<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Feature\PasswordChange;

use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Test\Feature\TestCase;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Ramsey\Uuid\Uuid;

class PasswordChangeTest extends TestCase {

    public function testEncryptionWorksAfterPasswordChange(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString()
        );

        /** @var EncryptionService $encryptionService */
        $encryptionService = $this->getService(EncryptionService::class);
        /** @var IKeyService $keyService */
        $keyService = $this->getService(IKeyService::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);

        $key       = $keyService->getKey($user);
        $raw       = Uuid::uuid4()->toString();
        $encrypted = $encryptionService->encrypt(
            $key,
            $raw
        );

        $newUser = clone $user;
        $newUser->setPassword(
            $userService->hashPassword(Uuid::uuid4()->toString())
        );

        $newUser = $userRepositoryService->updateUser($newUser, $user);

        $key          = $keyService->getKey($newUser);
        $decryptedRaw = $encryptionService->decrypt($key, $encrypted);
        $this->assertTrue($raw === $decryptedRaw);
    }

}
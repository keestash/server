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

namespace KST\Unit\Core\Service\Encryption\Key;

use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\Core\Repository\User\UserRepositoryTest;
use KST\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class KeyServiceTest extends TestCase {

    public function testCreateKey(): void {
        /** @var IKeyService $keyService */
        $keyService = $this->getService(IKeyService::class);
        /** @var ICredentialService $credentialService */
        $credentialService = $this->getService(ICredentialService::class);
        $keyHolder         = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $key               = $keyService->createKey(
            $credentialService->createCredentialFromDerivation(
                $keyHolder
            )
            , $keyHolder
        );
        $this->assertInstanceOf(IKey::class, $key);
    }

    public function testStoreKey(): void {
        /** @var IKeyService $keyService */
        $keyService = $this->getService(IKeyService::class);
        /** @var ICredentialService $credentialService */
        $credentialService = $this->getService(ICredentialService::class);
        $keyHolder         = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $key               = $keyService->createKey(
            $credentialService->createCredentialFromDerivation(
                $keyHolder
            )
            , $keyHolder
        );

        $this->assertInstanceOf(IKey::class, $key);

        $keyService->storeKey($keyHolder, $key);
    }

    public function testGetKey(): void {
        /** @var IKeyService $keyService */
        $keyService = $this->getService(IKeyService::class);
        $keyHolder  = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $key        = $keyService->getKey($keyHolder);
        $this->assertInstanceOf(IKey::class, $key);
    }

    public function testCreateAndStoreKey(): void {
        /** @var IKeyService $keyService */
        $keyService = $this->getService(IKeyService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        $user        = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => Uuid::uuid4() . '@keestash.com'
                    , 'last_name'  => UserRepositoryTest::class
                    , 'first_name' => UserRepositoryTest::class
                    , 'password'   => md5((string) time())
                    , 'phone'      => '0049123456789'
                    , 'website'    => 'https://keestash.com'
                    , 'locked'     => false
                    , 'deleted'    => false
                ]
            )
        );
        $key         = $keyService->createAndStoreKey($user);
        $this->assertInstanceOf(IKey::class, $key);
    }

}
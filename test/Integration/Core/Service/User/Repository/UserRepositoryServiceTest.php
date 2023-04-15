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

namespace KST\Integration\Core\Service\User\Repository;

use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class UserRepositoryServiceTest extends TestCase {

    private IUserRepositoryService $userRepositoryService;
    private IUserService           $userService;

    protected function setUp(): void {
        parent::setUp();
        $this->userRepositoryService = $this->getService(IUserRepositoryService::class);
        $this->userService           = $this->getService(IUserService::class);
    }

    public function testCreateAndRemoveUserAndUserExistsByNameAndUserExistsByMailAndUpdateUser(): void {
        $user = $this->userRepositoryService->createUser(
            $this->userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => Uuid::uuid4() . '@keestash.com'
                    , 'last_name'  => UserRepositoryServiceTest::class
                    , 'first_name' => UserRepositoryServiceTest::class
                    , 'password'   => md5((string) time())
                    , 'phone'      => '0049123456789'
                    , 'website'    => 'https://keestash.com'
                    , 'locked'     => false
                    , 'deleted'    => false
                ]
            )
        );

        $this->assertInstanceOf(IUser::class, $user);
        $this->assertTrue(
            true === $this->userRepositoryService->userExistsByName($user->getName())
        );
        $this->assertTrue(
            true === $this->userRepositoryService->userExistsByEmail($user->getEmail())
        );
        $newUser = clone $user;
        $newUser->setName($user->getName() . md5((string) time()));
        $updatedUser = $this->userRepositoryService->updateUser($newUser, $user);
        $this->assertInstanceOf(IUser::class, $updatedUser);
        $result = $this->userRepositoryService->removeUser($user);
        $this->assertTrue(true === $result['success']);
    }

}
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

namespace KST\Integration\Core\Repository\User;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Service\Service\UserService;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class UserRepositoryTest extends TestCase {

    public function testGetUser(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUser(UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME);
        $this->assertTrue($user instanceof IUser);
        $this->assertTrue($user->getId() === UserService::TEST_RESET_PASSWORD_USER_ID_7);
    }

    public function testGetNonExistingUser(): void {
        $this->expectException(UserNotFoundException::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $userRepository->getUser(md5((string) time()));
    }

    public function testGetUserByEmail(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);

        $user          = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => 'UserRepositoryTest' . Uuid::uuid4() . '@keestash.com'
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
        $retrievedUser = $userRepository->getUserByEmail($user->getEmail());
        $this->assertTrue($retrievedUser instanceof IUser);
        $this->assertTrue($retrievedUser->getId() === $user->getId());
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testGetNonExistingUserByEmail(): void {
        $this->expectException(UserNotFoundException::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $userRepository->getUserByEmail(md5((string) time()));
    }

    public function testGetUserByHash(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUserByHash($this->getUser()->getHash());
        $this->assertTrue($user instanceof IUser);
        $this->assertTrue($user->getId() === $this->getUser()->getId());
    }

    public function testGetNonExistingUserByHash(): void {
        $this->expectException(UserNotFoundException::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $userRepository->getUserByHash(md5((string) time()));
    }

    public function testGetAll(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $all            = $userRepository->getAll();
        $this->assertInstanceOf(ArrayList::class, $all);
    }

    public function testInsertUpdateAndRemove(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);

        $user          = $userRepositoryService->createUser( // we need to use the service instead of the repository
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
        $retrievedUser = $userRepository->getUserByEmail($user->getEmail());
        $this->assertTrue($retrievedUser instanceof IUser);
        $this->assertTrue($retrievedUser->getId() === $user->getId());

        $retrievedUser->setName(
            $retrievedUser->getName() . 'updated'
        );
        $updatedUser = $userRepository->update($retrievedUser);
        $this->assertTrue($updatedUser instanceof IUser);
        $this->assertTrue($updatedUser->getName() === $retrievedUser->getName());

        $result = $userRepositoryService->removeUser($updatedUser);
        $this->assertTrue($result['user_removed'] instanceof IUser);
    }

    public function testSearchUsers(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);

        $usersToDelete = new ArrayList();
        foreach ([1, 2, 3] as $id) {
            $user = $userRepository->insert(
                $userService->toNewUser(
                    [
                        'user_name'    => UserRepositoryTest::class . Uuid::uuid4()->toString()
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
            $usersToDelete->add($user);
        }

        $usersFound = $userRepository->searchUsers(UserRepositoryTest::class);
        $this->assertTrue($usersFound->length() === 3);

        foreach ($usersToDelete as $user) {
//            $userRepository->remove($user);
            $userRepositoryService->removeUser($user);
        }
    }

}
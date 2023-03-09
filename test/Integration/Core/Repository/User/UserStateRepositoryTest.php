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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\TestCase;
use Ramsey\Uuid\Uuid;

class UserStateRepositoryTest extends TestCase {

    public function testLockAndUnlock(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $user = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => Uuid::uuid4() . '@keestash.com'
                    , 'last_name'  => UserStateRepositoryTest::class
                    , 'first_name' => UserStateRepositoryTest::class
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

        $userStateRepository->lock($retrievedUser);
        $userStateRepository->unlock($retrievedUser);
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testLockedUsers(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $userList = new HashTable();
        foreach ([1, 2, 3] as $id) {
            $user = $userRepositoryService->createUser(
                $userService->toNewUser(
                    [
                        'user_name'    => Uuid::uuid4()->toString()
                        , 'email'      => Uuid::uuid4() . '@keestash.com'
                        , 'last_name'  => UserStateRepositoryTest::class
                        , 'first_name' => UserStateRepositoryTest::class
                        , 'password'   => md5((string) time())
                        , 'phone'      => '0049123456789'
                        , 'website'    => 'https://keestash.com'
                        , 'locked'     => false
                        , 'deleted'    => false
                    ]
                )
            );
            $userStateRepository->lock($user);
            $userList->put($user->getId(), $user);
        }

        $lockedUsers = $userStateRepository->getLockedUsers();
        $counter     = 0;
        /** @var IUserState $userState */
        foreach ($lockedUsers->toArray() as $userState) {
            $user = $userList->get($userState->getUser()->getId());

            if (null === $user) {
                continue; // it can be the locked user with id 4
            }

            $this->assertTrue($userState->getUser()->getId() === $user->getId());
            $counter++;
        }

        $this->assertTrue($userList->size() === $counter);

        foreach ($userList as $user) {
            $userRepositoryService->removeUser($user);
        }
    }

    public function testDeleteAndRevertDelete(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $user          = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => Uuid::uuid4() . '@keestash.com'
                    , 'last_name'  => UserStateRepositoryTest::class
                    , 'first_name' => UserStateRepositoryTest::class
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

        $userStateRepository->delete($retrievedUser);
        $userStateRepository->revertDelete($retrievedUser);
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testDeletedUsers(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $userList = new HashTable();
        foreach ([1, 2, 3] as $id) {
            $user = $userRepositoryService->createUser(
                $userService->toNewUser(
                    [
                        'user_name'    => UserStateRepositoryTest::class . $id
                        , 'email'      => 'UserStateRepositoryTest' . $id . '@keestash.com'
                        , 'last_name'  => UserStateRepositoryTest::class
                        , 'first_name' => UserStateRepositoryTest::class
                        , 'password'   => md5((string) time())
                        , 'phone'      => '0049123456789'
                        , 'website'    => 'https://keestash.com'
                        , 'locked'     => false
                        , 'deleted'    => false
                    ]
                )
            );
            $userStateRepository->delete($user);
            $userList->put($user->getId(), $user);
        }

        $deletedUsers = $userStateRepository->getDeletedUsers();
        $counter      = 0;
        /** @var IUserState $userState */
        foreach ($deletedUsers->toArray() as $userState) {
            $user = $userList->get($userState->getUser()->getId());

            if (null === $user) {
                continue; // it can be the locked user with id 4
            }

            $this->assertTrue($userState->getUser()->getId() === $user->getId());
            $counter++;
        }

        $this->assertTrue($userList->size() === $counter);

        foreach ($userList as $user) {
            $userRepositoryService->removeUser($user);
        }
    }

    public function testRequestPasswordChangeAndRevert(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $user          = $userRepositoryService->createUser(
            $userService->toNewUser(
                [
                    'user_name'    => Uuid::uuid4()->toString()
                    , 'email'      => Uuid::uuid4() . '@keestash.com'
                    , 'last_name'  => UserStateRepositoryTest::class
                    , 'first_name' => UserStateRepositoryTest::class
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

        $hash = Uuid::uuid4()->toString();
        $userStateRepository->requestPasswordReset($retrievedUser, $hash);
        $userStateRepository->revertPasswordChangeRequest($retrievedUser);

        $userRepositoryService->removeUser($retrievedUser);
    }

//
    public function testUsersWithPasswordReset(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);

        $userList = new HashTable();
        foreach ([1, 2, 3] as $id) {
            $user = $userRepositoryService->createUser(
                $userService->toNewUser(
                    [
                        'user_name'    => Uuid::uuid4()->toString()
                        , 'email'      => Uuid::uuid4()->toString() . '@keestash.com'
                        , 'last_name'  => UserStateRepositoryTest::class
                        , 'first_name' => UserStateRepositoryTest::class
                        , 'password'   => md5((string) time())
                        , 'phone'      => '0049123456789'
                        , 'website'    => 'https://keestash.com'
                        , 'locked'     => false
                        , 'deleted'    => false
                    ]
                )
            );
            $userStateRepository->requestPasswordReset($user, Uuid::uuid4()->toString());
            $userList->put($user->getId(), $user);
        }

        $usersWithPasswordResetRequest = $userStateRepository->getUsersWithPasswordResetRequest();
        $counter                       = 0;
        /** @var IUserState $userState */
        foreach ($usersWithPasswordResetRequest->toArray() as $userState) {
            $user = $userList->get($userState->getUser()->getId());

            if (null === $user) {
                continue; // it can be the locked user with id 4
            }

            $this->assertTrue($userState->getUser()->getId() === $user->getId());
            $counter++;
        }

        $this->assertTrue($userList->size() === $counter);

        foreach ($userList as $user) {
            $userRepositoryService->removeUser($user);
        }
    }

}
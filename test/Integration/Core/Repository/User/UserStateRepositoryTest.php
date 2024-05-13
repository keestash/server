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

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\User\UserState;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class UserStateRepositoryTest extends TestCase {

    public function testLockAndUnlock(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);
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

        $userStateService->forceLock($retrievedUser);
        $userStateService->clear($retrievedUser);
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testLockedUsers(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

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

            $userStateService->forceLock($user);
            $userList->put($user->getId(), $user);
        }

        foreach ($userList->toArray() as $user) {
            $userState = $userStateService->getState($user);
            $this->assertTrue($userState->getState() === IUserState::USER_STATE_LOCK);
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
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

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

        $userStateService->forceDelete($retrievedUser);
        $userStateService->clear($retrievedUser);
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testDeletedUsers(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

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
            $userStateService->forceDelete($user);
            $userList->put($user->getId(), $user);
        }

        foreach ($userList->toArray() as $user) {
            $userState = $userStateService->getState($user);
            $this->assertTrue($userState->getState() === IUserState::USER_STATE_DELETE);
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
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

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
        $userStateService->setState(
            new UserState(
                0,
                $retrievedUser,
                IUserState::USER_STATE_REQUEST_PW_CHANGE,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                $hash
            )
        );
        $userStateService->clearCarefully($user, IUserState::USER_STATE_REQUEST_PW_CHANGE);
        $userRepositoryService->removeUser($retrievedUser);
    }

    public function testUsersWithPasswordReset(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

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
            $userStateService->setState(
                new UserState(
                    0,
                    $user,
                    IUserState::USER_STATE_REQUEST_PW_CHANGE,
                    new DateTimeImmutable(),
                    new DateTimeImmutable(),
                    Uuid::uuid4()->toString()
                )
            );

            $userList->put($user->getId(), $user);
        }

        foreach ($userList->toArray() as $user) {
            $userState = $userStateRepository->getByUser($user);
            $this->assertTrue($userState->getState() === IUserState::USER_STATE_REQUEST_PW_CHANGE);
            $userRepositoryService->removeUser($user);
        }

    }

}

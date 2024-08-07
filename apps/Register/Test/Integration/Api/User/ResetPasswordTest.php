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

namespace KSA\Register\Test\Integration\Api\User;

use Keestash\Core\DTO\User\UserState;
use Keestash\Core\DTO\User\UserStateName;
use KSA\Register\Api\User\ResetPassword;
use KSA\Register\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Integration\Core\Repository\User\UserRepositoryTest;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class ResetPasswordTest extends TestCase {

    public function testWithoutInput(): void {
        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithInvalidInput(): void {
        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest(
                [
                    'input' => 'NoValidInput'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithDisabledUser(): void {
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
                    , 'locked'     => true
                    , 'deleted'    => false
                ]
            )
        );

        $this->assertInstanceOf(IUser::class, $user);
        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest(
                [
                    'input' => $user->getName()
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
        $userRepositoryService->removeUser($user);
    }

    public function testWithAlreadyRequested(): void {
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);
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
        $this->assertInstanceOf(IUser::class, $user);

        $userStateService->setState(
            new UserState(
                0,
                $user,
                UserStateName::REQUEST_PW_CHANGE,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                $user->getHash()
            )
        );

        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest(
                [
                    'input' => $user->getName()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
        $userRepositoryService->removeUser($user);
    }


    public function testRegularCase(): void {
        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest(
                [
                    'input' => UserService::TEST_USER_ID_2_NAME
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

    public function testWithEmailAddress(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString()
        );
        /** @var ResetPassword $forgotPassword */
        $forgotPassword = $this->getService(ResetPassword::class);
        $response       = $forgotPassword->handle(
            $this->getVirtualRequest(
                [
                    'input' => $user->getEmail()
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->removeUser($user);
    }

}

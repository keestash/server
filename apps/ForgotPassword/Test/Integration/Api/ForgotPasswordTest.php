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

namespace KSA\ForgotPassword\Test\Integration\Api;

use KSA\ForgotPassword\Api\ForgotPassword;
use KSA\ForgotPassword\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KST\Service\Service\UserService;

class ForgotPasswordTest extends TestCase {

    public function testWithoutInput(): void {
        /** @var ForgotPassword $forgotPassword */
        $forgotPassword = $this->getService(ForgotPassword::class);
        $response       = $forgotPassword->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithInvalidInput(): void {
        /** @var ForgotPassword $forgotPassword */
        $forgotPassword = $this->getService(ForgotPassword::class);
        $response       = $forgotPassword->handle(
            $this->getDefaultRequest(
                [
                    'input' => 'NoValidInput'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithDisabledUser(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        $user                = $userRepository->getUserById((string) UserService::TEST_PASSWORD_FORGOT_USER_ID_6);
        $this->assertInstanceOf(IUser::class, $user);
        $userStateRepository->lock($user);
        /** @var ForgotPassword $forgotPassword */
        $forgotPassword = $this->getService(ForgotPassword::class);
        $response       = $forgotPassword->handle(
            $this->getDefaultRequest(
                [
                    'input' => UserService::TEST_PASSWORD_FORGOT_USER_ID_6_NAME
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testWithAlreadyRequested(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        $user                = $userRepository->getUserById((string) UserService::TEST_PASSWORD_FORGOT_USER_ID_6);
        $this->assertInstanceOf(IUser::class, $user);
        $userStateRepository->revertPasswordChangeRequest($user);
        $userStateRepository->unlock($user);
        $userStateRepository->requestPasswordReset($user, $user->getHash());

        /** @var ForgotPassword $forgotPassword */
        $forgotPassword = $this->getService(ForgotPassword::class);
        $response       = $forgotPassword->handle(
            $this->getDefaultRequest(
                [
                    'input' => UserService::TEST_PASSWORD_FORGOT_USER_ID_6_NAME
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }


    public function testRegularCase(): void {
        /** @var ForgotPassword $forgotPassword */
        $forgotPassword = $this->getService(ForgotPassword::class);
        $response       = $forgotPassword->handle(
            $this->getDefaultRequest(
                [
                    'input' => UserService::TEST_USER_ID_2_NAME
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

}
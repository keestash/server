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

use KSA\ForgotPassword\Api\ResetPassword;
use KSA\ForgotPassword\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class ResetPasswordTest extends TestCase {

    public function testWithNoUserFound(): void {
        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getService(ResetPassword::class);
        $response      = $resetPassword->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithMinimumPasswordsAreNotSet(): void {
        $hash = Uuid::uuid4();
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        $user                = $userRepository->getUserById((string) UserService::TEST_RESET_PASSWORD_USER_ID_7);
        $this->assertInstanceOf(IUser::class, $user);
        $userStateRepository->requestPasswordReset($user, (string) $hash);

        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getService(ResetPassword::class);
        $response      = $resetPassword->handle(
            $this->getDefaultRequest(
                [
                    'hash'    => (string) $hash
                    , 'input' => 'unsafe'
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        $hash = Uuid::uuid4();
        /** @var IPasswordService $passwordService */
        $passwordService = $this->getService(IPasswordService::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        $user                = $userRepository->getUserById((string) UserService::TEST_RESET_PASSWORD_USER_ID_7);
        $this->assertInstanceOf(IUser::class, $user);
        $userStateRepository->revertPasswordChangeRequest($user);
        $userStateRepository->unlock($user);
        $userStateRepository->requestPasswordReset($user, (string) $hash);

        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getService(ResetPassword::class);
        $response      = $resetPassword->handle(
            $this->getDefaultRequest(
                [
                    'hash'    => (string) $hash
                    , 'input' => $passwordService->generatePassword(20, true, true, true, true)->getValue()
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }


}
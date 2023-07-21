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
use KSA\ForgotPassword\ConfigProvider;
use KSA\ForgotPassword\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KST\Service\Service\UserService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class ResetPasswordTest extends TestCase {

    public function testWithNoUserFound(): void {
        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getService(ResetPassword::class);
        $response      = $resetPassword->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
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
            $this->getVirtualRequest(
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
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        /** @var LoggerInterface $logger */
        $logger = $this->getService(LoggerInterface::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $userStateRepository->requestPasswordReset($user, Uuid::uuid4()->toString());
        $this->assertInstanceOf(IUser::class, $user);
        $userStateRepository->revertPasswordChangeRequest($user);
        $userStateRepository->requestPasswordReset($user, (string) $hash);

        /** @var ResetPassword $resetPassword */
        $resetPassword = $this->getService(ResetPassword::class);
        $input         = $this->getVirtualRequest(
            [
                'hash'    => (string) $hash
                , 'input' => $passwordService->generatePassword(20, true, true, true, true)->getValue()
            ]
        );
        $response      = $resetPassword->handle($input);

        $validResponse = true === $this->getResponseService()->isValidResponse($response);
        if (false === $validResponse) {
            $logger->error('should not happen, response is invalid', ['response' => $response, 'input' => $input]);
        }
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

    public function testWithNonExistingUser(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::RESET_PASSWORD_UPDATE
                    , [
                        'hash'    => Uuid::uuid4()->toString()
                        , 'input' => Uuid::uuid4()->toString()
                    ]
                    , $user
                    , $headers
                )
            );
        $data     = $this->getDecodedData($response);
        $this->assertArrayHasKey("responseCode", $data);
        $this->assertTrue($data['responseCode'] === 133909);
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }


}
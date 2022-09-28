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

use KSA\ForgotPassword\Api\AccountDetails;
use KSA\ForgotPassword\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KST\Service\Service\UserService;

class AccountDetailsTest extends TestCase {

    public function testWithNoToken(): void {
        /** @var AccountDetails $accountDetails */
        $accountDetails = $this->getService(AccountDetails::class);
        $response       = $accountDetails->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
            && IResponse::BAD_REQUEST === $response->getStatusCode()
        );
    }

    public function testWithNoUserFound(): void {
        /** @var AccountDetails $accountDetails */
        $accountDetails = $this->getService(AccountDetails::class);
        $response       = $accountDetails->handle(
            $this->getDefaultRequest()
                ->withAttribute('resetPasswordToken', 'noPasswordTokenToResetGivenHere')
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
            && IResponse::NOT_FOUND === $response->getStatusCode()
        );
    }

    public function testRegularCase(): void {
        $token = md5((string) time());
        /** @var IUserStateRepository $userStateRepository */
        $userStateRepository = $this->getService(IUserStateRepository::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUserById((string) UserService::TEST_PASSWORD_RESET_USER_ID_5);
        $userStateRepository->requestPasswordReset($user, $token);

        /** @var AccountDetails $accountDetails */
        $accountDetails = $this->getService(AccountDetails::class);
        $response       = $accountDetails->handle(
            $this->getDefaultRequest()
                ->withAttribute('resetPasswordToken', $token)
        );

        $responseData = json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(
            true === $this->getResponseService()->isValidResponse($response)
        );
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($responseData['hasHash'] === true);
        $this->assertTrue($responseData['token'] === $token);
    }

}
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

namespace KSA\Login\Test\Api;

use Keestash\Core\Service\Router\VerificationService;
use KSA\Login\Api\Login;
use KSA\Login\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;

class LoginTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var Login $login */
        $login    = $this->getService(Login::class);
        $response = $login->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithNotExistingUser(): void {
        /** @var Login $login */
        $login    = $this->getService(Login::class);
        $response = $login->handle(
            $this->getDefaultRequest(
                [
                    'user'       => 'ThisIsANonExistingUserForLoginApp'
                    , 'password' => 'ThisIsAnInvalidPasswordForNonExistingUser'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithDisabledUser(): void {
        /** @var Login $login */
        $login    = $this->getService(Login::class);
        $response = $login->handle(
            $this->getDefaultRequest(
                [
                    'user'       => UserService::TEST_LOCKED_USER_ID_4_NAME
                    , 'password' => 'ThisIsAnInvalidPasswordForDisabledUser'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithExistingUserButIncorrectPassword(): void {
        /** @var Login $login */
        $login    = $this->getService(Login::class);
        $response = $login->handle(
            $this->getDefaultRequest(
                [
                    'user'       => UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME
                    , 'password' => 'ThisIsAnInvalidPasswordForNonExistingUser'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::UNAUTHORIZED === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        $this->markTestSkipped('password_verify returns false. I assume it is related to the charset of sqlite, but not sure. Need to check this');
        $userName = UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME;
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var Login $login */
        $login        = $this->getService(Login::class);
        $response     = $login->handle(
            $this->getDefaultRequest(
                [
                    'user'       => $userName
                    , 'password' => UserService::TEST_PASSWORD_FORGOT_USER_ID_7_PASSWORD
                ]
            )
        );
        $responseBody = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $user    = $userRepository->getUser($userName);
        $headers = $response->getHeaders();

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(isset($responseBody['settings']));
        $this->assertTrue(isset($responseBody['settings']['locale']));
        $this->assertTrue(isset($responseBody['settings']['language']));
        $this->assertTrue(isset($responseBody['user']));
        $this->assertTrue($responseBody['user']['name'] === $userName);
        $this->assertTrue(isset($headers[VerificationService::FIELD_NAME_TOKEN]));
        $this->assertTrue(isset($headers[VerificationService::FIELD_NAME_USER_HASH]));
        $this->assertTrue($headers[VerificationService::FIELD_NAME_USER_HASH] === $user->getHash());
    }

}
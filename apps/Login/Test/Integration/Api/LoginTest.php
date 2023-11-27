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

namespace KSA\Login\Test\Integration\Api;

use Keestash\Core\Service\Router\VerificationService;
use KSA\Login\ConfigProvider;
use KSA\Login\Entity\IResponseCodes;
use KSA\Login\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\Repository\User\IUserRepository;
use Ramsey\Uuid\Uuid;

class LoginTest extends TestCase {

    public function testWithNoParameters(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::LOGIN_SUBMIT
                    , [
                        'user'       => 'ThisIsANonExistingUserForLoginApp'
                        , 'password' => 'ThisIsAnInvalidPasswordForNonExistingUser'
                    ]
                )
            );
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->assertResponseCode(IResponseCodes::RESPONSE_CODE_USER_NOT_FOUND, $response);
    }

    public function testWithNotExistingUser(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::LOGIN_SUBMIT
                    , [
                        'user'       => 'ThisIsANonExistingUserForLoginApp'
                        , 'password' => 'ThisIsAnInvalidPasswordForNonExistingUser'
                    ]
                )
            );

        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->assertResponseCode(IResponseCodes::RESPONSE_CODE_USER_NOT_FOUND, $response);
    }

    public function testWithDisabledUser(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
            , true
        );
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::LOGIN_SUBMIT
                    , [
                        'user'       => $user->getName()
                        , 'password' => 'ThisIsAnInvalidPasswordForDisabledUser'
                    ]
                    , $user
                )
            );

        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->assertResponseCode(IResponseCodes::RESPONSE_CODE_USER_DISABLED, $response);
        $this->removeUser($user);
    }

    public function testWithExistingUserButIncorrectPassword(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::LOGIN_SUBMIT
                    , [
                        'user'       => $user->getName()
                        , 'password' => 'ThisIsAnInvalidPasswordForNonExistingUser'
                    ]
                    , $user
                )
            );

        $this->assertStatusCode(IResponse::UNAUTHORIZED, $response);
        $this->assertResponseCode(IResponseCodes::RESPONSE_CODE_INVALID_CREDENTIALS, $response);
        $this->removeUser($user);
    }

    public function testRegularCase(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::LOGIN_SUBMIT
                    , [
                        'user'       => $user->getName()
                        , 'password' => $password
                    ]
                    , $user
                )
            );

        $responseBody = $this->getDecodedData($response);

        $user            = $userRepository->getUser($user->getName());
        $responseHeaders = $response->getHeaders();

        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertTrue(isset($responseBody['settings']));
        $this->assertTrue(isset($responseBody['settings']['locale']));
        $this->assertTrue(isset($responseBody['settings']['language']));
        $this->assertTrue(isset($responseBody['user']));
        $this->assertTrue($responseBody['user']['name'] === $user->getName());
        $this->assertTrue(isset($responseHeaders[VerificationService::FIELD_NAME_TOKEN]));
        $this->assertTrue(isset($responseHeaders[VerificationService::FIELD_NAME_USER_HASH]));
        $this->assertTrue($responseHeaders[VerificationService::FIELD_NAME_USER_HASH][0] === $user->getHash());
    }

}
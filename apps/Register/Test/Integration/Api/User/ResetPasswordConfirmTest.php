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
use KSA\Register\ConfigProvider;
use KSA\Register\Entity\IResponseCodes;
use KSA\Register\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Service\User\IUserStateService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class ResetPasswordConfirmTest extends TestCase {

    public function testWithNoUserFound(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::RESET_PASSWORD_CONFIRM
                    , []
                )
            );

        $decoded = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->assertTrue($decoded['responseCode'] === IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND);
    }

    public function testWithMinimumPasswordsAreNotSet(): void {
        $hash = (string) Uuid::uuid4();
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $this->assertInstanceOf(IUser::class, $user);

        $userStateService->setState(
            new UserState(
                0,
                $user,
                IUserState::USER_STATE_REQUEST_PW_CHANGE,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                $hash
            )
        );

        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::RESET_PASSWORD_CONFIRM
                    , [
                        'hash' => $hash,
                    ]
                )
            );

        $decoded = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertTrue($decoded['responseCode'] === IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD);
        $this->removeUser($user);
    }

    public function testRegularCase(): void {
        $hash = Uuid::uuid4();
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);
        /** @var LoggerInterface $logger */
        $logger = $this->getService(LoggerInterface::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $userStateService->setState(
            new UserState(
                0,
                $user,
                IUserState::USER_STATE_REQUEST_PW_CHANGE,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                Uuid::uuid4()->toString()
            )
        );

        $this->assertInstanceOf(IUser::class, $user);
        $userStateService->clearCarefully($user, IUserState::USER_STATE_REQUEST_PW_CHANGE);

        $userStateService->setState(
            new UserState(
                0,
                $user,
                IUserState::USER_STATE_REQUEST_PW_CHANGE,
                new \DateTimeImmutable(),
                new \DateTimeImmutable(),
                (string) $hash
            )
        );

        $headers  = $this->login($user, $password);
        $input    = [
            'hash'       => (string) $hash
            , 'password' => 'Rtyfgdsfsf124?dfgdfgfdxcSFGISEIJFefsgfdrgwer2345@!3445'
        ];
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::RESET_PASSWORD_CONFIRM
                    , $input
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);
        // temporary, since sometimes an error occurs, sometimes not
        if (IResponse::OK !== $response->getStatusCode()) {
            $logger->error(
                'should not happen, response is invalid',
                ['response' => (string) $response->getBody()
                 , 'input'  => $input
                ]
            );
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
                    , ConfigProvider::RESET_PASSWORD_CONFIRM
                    , [
                        'hash'       => Uuid::uuid4()->toString()
                        , 'password' => Uuid::uuid4()->toString()
                    ]
                    , $user
                    , $headers
                )
            );
        $data     = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::NOT_FOUND, $response);
        $this->assertArrayHasKey("responseCode", $data);
        $this->assertTrue($data['responseCode'] === IResponseCodes::RESPONSE_CODE_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }


}

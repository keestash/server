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

namespace KSA\Settings\Test\Api\User;

use KSA\Settings\Api\User\Get;
use KSA\Settings\Test\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;

class GetTest extends TestCase {

    public function testWithMissingParameters(): void {
        /** @var Get $get */
        $get      = $this->getService(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithMissingNonExistingUser(): void {
        /** @var Get $get */
        $get      = $this->getService(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()
                ->withAttribute('userHash', md5((string) time()))
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);

        $user = $userService->toNewUser(
            [
                'user_name'    => md5((string) time())
                , 'email'      => md5((string) time()) . '@keestash.com'
                , 'last_name'  => GetTest::class
                , 'first_name' => GetTest::class
                , 'password'   => md5((string) time())
                , 'phone'      => '0049691234567'
                , 'website'    => 'keestash.com'
                , 'locked'     => false
                , 'deleted'    => false
            ]
        );
        $user = $userRepositoryService->createUser($user);

        /** @var Get $get */
        $get          = $this->getService(Get::class);
        $response     = $get->handle(
            $this->getDefaultRequest()
                ->withAttribute('userHash', $user->getHash())
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($responseBody['user']['id'] === $user->getId());
        $this->assertTrue($responseBody['user']['hash'] === $user->getHash());
        $userRepositoryService->removeUser($user);
    }

}
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

use KSA\Register\Test\TestCase;
use KSA\Settings\Api\User\UserEdit;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Service\UserService;

class UserEditTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);

        $response = $userEdit->handle(
            $this->getDefaultRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithNoPermission(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);

        $response = $userEdit->handle(
            $this->getDefaultRequest(
                [
                    'id' => UserService::TEST_PASSWORD_RESET_USER_ID_5
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testWithMissingData(): void {
        $this->expectWarning();
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);

        $response = $userEdit->handle(
            $this->getDefaultRequest(
                [
                    'id'           => UserService::TEST_USER_ID_2
                    , 'first_name' => UserEdit::class
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUserById((string) UserService::TEST_USER_ID_2);
        $response       = $userEdit->handle(
            $this->getDefaultRequest(
                [
                    'id'           => $user->getId()
                    , 'name'       => $user->getName()
                    , 'first_name' => $user->getFirstName()
                    , 'last_name'  => $user->getLastName()
                    , 'email'      => $user->getEmail()
                    , 'phone'      => $user->getPhone()
                    , 'locked'     => $user->isLocked()
                    , 'deleted'    => $user->isDeleted()
                    , 'language'   => $user->getLanguage()
                    , 'locale'     => $user->getLocale()
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

}
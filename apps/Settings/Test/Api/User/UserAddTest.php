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
use KSA\Settings\Api\User\UserAdd;
use KSA\Settings\Test\Api\Organization\AddTest;
use KSP\Api\IResponse;
use Ramsey\Uuid\Uuid;

class UserAddTest extends TestCase {

    public function testWithEmptyRequest(): void {
        if (PHP_VERSION_ID > 70429) {
            $this->expectWarning();
        }
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithExistingUser(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => UserAdd::class
                    , 'last_name'            => UserAdd::class
                    , 'user_name'            => 'Keestash'
                    , 'email'                => UserAdd::class . '@keestash.com'
                    , 'password'             => ''
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithNonMatchingPasswords(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => AddTest::class
                    , 'email'                => 'dev.null.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithMinimumRequirementsNotMet(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => AddTest::class
                    , 'email'                => 'dev.null.com'
                    , 'password'             => 'qwerty'
                    , 'password_repeat'      => 'qwerty'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithNonRegularEmail(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => AddTest::class
                    , 'email'                => 'dev.null.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithNonRegularWebsite(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => Uuid::uuid4()->toString()
                    , 'email'                => Uuid::uuid4() . '@keestash.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash-com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var UserAdd $userAdd */
        $userAdd = $this->getService(UserAdd::class);

        $response = $userAdd->handle(
            $this->getDefaultRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => Uuid::uuid4()->toString()
                    , 'email'                => Uuid::uuid4() . '@keestash.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'https://keestash.com'
                    , 'locked'               => false
                    , 'deleted'              => false
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

}
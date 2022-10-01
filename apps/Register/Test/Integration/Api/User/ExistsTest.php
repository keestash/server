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

use KSA\Register\Api\User\Exists;
use KSA\Register\Test\TestCase;
use KST\Service\Service\UserService;

class ExistsTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var Exists $exists */
        $exists       = $this->getService(Exists::class);
        $response     = $exists->handle(
            $this->getDefaultRequest()
        );
        $responseBody = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(false === $responseBody['user_exists']);
    }

    public function testWithExistingUser(): void {
        /** @var Exists $exists */
        $exists       = $this->getService(Exists::class);
        $response     = $exists->handle(
            $this->getDefaultRequest()
            ->withAttribute('userName' , UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME)
        );
        $responseBody = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(true === $responseBody['user_exists']);
    }

}
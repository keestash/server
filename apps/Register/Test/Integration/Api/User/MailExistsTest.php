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

use Keestash\Core\System\Application;
use KSA\Register\Api\User\MailExists;
use KSA\Register\Test\Integration\TestCase;
use KST\Service\Service\UserService;

class MailExistsTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var MailExists $exists */
        $exists       = $this->getService(MailExists::class);
        $response     = $exists->handle(
            $this->getVirtualRequest()
        );
        $responseBody = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(false === $responseBody['email_address_exists']);
    }

    public function testWithExistingUser(): void {
        /** @var Application $legacy */
        $legacy = $this->getService(Application::class);
        /** @var MailExists $exists */
        $exists       = $this->getService(MailExists::class);
        $response     = $exists->handle(
            $this->getVirtualRequest()
                ->withAttribute('address', UserService::TEST_PASSWORD_RESET_USER_ID_5_EMAIL)
        );
        $responseBody = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(true === $responseBody['email_address_exists']);
    }

}
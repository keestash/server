<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Attachment;

use KSA\PasswordManager\Api\Node\Attachment\Get;
use KSP\Api\IResponse;
use KST\TestCase;

class GetTest extends TestCase {

    public function testWithoutId(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNonExisting(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()->withAttribute('nodeId', 99999)
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNoAccess(): void {
        $this->markTestSkipped('we need a way to run these handlers using the middlewars');
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()->withAttribute('nodeId', 5)
        );
        $this->assertTrue(
            false === $this->getResponseService()->isValidResponse($response)
            && $response->getStatusCode() === IResponse::FORBIDDEN
        );
    }

    public function testGet(): void {
        /** @var Get $get */
        $get      = $this->getServiceManager()->get(Get::class);
        $response = $get->handle(
            $this->getDefaultRequest()->withAttribute('nodeId', 2)
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
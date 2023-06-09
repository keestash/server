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

namespace KSA\Instance\Test\Integration\Api\Demo;

use Keestash\Exception\Validator\ValidationFailedException;
use KSA\Instance\Api\Demo\AddEmailAddress;
use KSA\Instance\Repository\DemoUsersRepository;
use KSA\Instance\Test\Integration\TestCase;
use KSP\Api\IResponse;

class AddEmailAddressTest extends TestCase {

    public function testWithInvalidEmailAddress(): void {
        $this->expectException(ValidationFailedException::class);
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        $response        = $addEmailAddress->handle(
            $this->getVirtualRequest(
                [
                    'email' => 'invalidEmailAddress'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::INTERNAL_SERVER_ERROR === $response->getStatusCode());
    }

    public function testWithEmptyEmail(): void {
        $this->expectException(ValidationFailedException::class);
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        $response        = $addEmailAddress->handle(
            $this->getVirtualRequest([])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::INTERNAL_SERVER_ERROR === $response->getStatusCode());
    }

    public function testWithValidMail(): void {
        $address = 'dev@null.com';
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        /** @var DemoUsersRepository $demoUsersRepository */
        $demoUsersRepository = $this->getService(DemoUsersRepository::class);
        $response            = $addEmailAddress->handle(
            $this->getVirtualRequest(
                [
                    'email' => $address
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(true === $demoUsersRepository->hasEmailAddress($address));
    }

    public function testWithExistingEmail():void {
        /** @var DemoUsersRepository $demoUserRepository */
        $demoUserRepository = $this->getService(DemoUsersRepository::class);
        $demoUserRepository->add('dev@null.de');
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        $response = $addEmailAddress->handle(
            $this->getVirtualRequest(
                [
                    'email' => 'dev@null.de'
                ]
            )
        );

        $this->assertStatusCode(IResponse::CONFLICT, $response);
    }

}
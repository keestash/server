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

namespace KSA\GeneralApi\Test\Integration\Api\Demo;

use KSA\GeneralApi\Api\Demo\AddEmailAddress;
use KSA\GeneralApi\Exception\GeneralApiException;
use KSA\GeneralApi\Test\TestCase;
use KSA\Settings\Repository\DemoUsersRepository;
use KSP\Api\IResponse;

class AddEmailAddressTest extends TestCase {

    public function testWithInvalidEmailAddress(): void {
        $this->expectException(GeneralApiException::class);
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        $response        = $addEmailAddress->handle(
            $this->getDefaultRequest(
                [
                    'email' => 'invalidEmailAddress'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::INTERNAL_SERVER_ERROR === $response->getStatusCode());
    }

    public function testWithEmptyEmail(): void {
        $this->expectException(GeneralApiException::class);
        /** @var AddEmailAddress $addEmailAddress */
        $addEmailAddress = $this->getService(AddEmailAddress::class);
        $response        = $addEmailAddress->handle(
            $this->getDefaultRequest([])
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
            $this->getDefaultRequest(
                [
                    'email' => $address
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(true === $demoUsersRepository->hasEmailAddress($address));
    }

}
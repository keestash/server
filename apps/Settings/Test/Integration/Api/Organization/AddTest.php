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

namespace KSA\Settings\Test\Integration\Api\Organization;

use KSA\Settings\Api\Organization\Add;
use KSA\Settings\Test\Integration\TestCase;

class AddTest extends TestCase {

    public function testWithNoParameters(): void {
        /** @var Add $add */
        $add      = $this->getService(Add::class);
        $response = $add->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithName(): void {
        $name = AddTest::class;
        /** @var Add $add */
        $add          = $this->getService(Add::class);
        $response     = $add->handle(
            $this->getVirtualRequest(
                [
                    'organization' => $name
                ]
            )
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue($name === $responseBody['organization']['name']);
    }

}
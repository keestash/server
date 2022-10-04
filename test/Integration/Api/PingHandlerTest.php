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

namespace KST\Integration\Api;

use Keestash\Api\PingHandler;
use KSP\Api\IResponse;
use KST\TestCase;

class PingHandlerTest extends TestCase {

    public function testPingHandler(): void {
        /** @var PingHandler $pingHandler */
        $pingHandler  = $this->getService(PingHandler::class);
        $response     = $pingHandler->handle(
            $this->getDefaultRequest()
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(is_numeric($responseBody[0]));
    }

}
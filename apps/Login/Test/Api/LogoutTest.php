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

namespace KSA\Login\Test\Api;

use KSA\Login\Api\Logout;
use KSA\Login\Test\TestCase;
use KSP\Api\IResponse;

class LogoutTest extends TestCase {

    public function testLogout(): void {
        /** @var Logout $logout */
        $logout   = $this->getService(Logout::class);
        $response = $logout->handle(
            $this->getDefaultRequest()
        );
        $this->assertValidResponse($response);
        $this->assertStatusCode(IResponse::OK, $response);
    }

}
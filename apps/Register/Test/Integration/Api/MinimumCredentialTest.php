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

namespace KSA\Register\Test\Integration\Api;

use KSA\Register\Api\MinimumCredential;
use KSA\Register\Test\TestCase;
use KSP\Api\IResponse;

class MinimumCredentialTest extends TestCase {

    public function testWithoutParameters(): void {
        /** @var MinimumCredential $minimumCredential */
        $minimumCredential = $this->getService(MinimumCredential::class);
        $response          = $minimumCredential->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithMalformedData(): void {
        /** @var MinimumCredential $minimumCredential */
        $minimumCredential = $this->getService(MinimumCredential::class);
        $response          = $minimumCredential->handle(
            $this->getDefaultRequest()
                ->withQueryParams(
                    [
                        'password' => '<SCRIPT SRC=http://xss.rocks/xss.js></SCRIPT>'
                    ]
                )
        );
        $responseBody      = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(false === $responseBody['valid']);
    }

    public function testWithInteger(): void {
        /** @var MinimumCredential $minimumCredential */
        $minimumCredential = $this->getService(MinimumCredential::class);
        $response          = $minimumCredential->handle(
            $this->getDefaultRequest()
                ->withQueryParams(
                    [
                        'password' => 123456
                    ]
                )
        );
        $responseBody      = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(false === $responseBody['valid']);
    }

    public function testWithValidPassword(): void {
        /** @var MinimumCredential $minimumCredential */
        $minimumCredential = $this->getService(MinimumCredential::class);
        $response          = $minimumCredential->handle(
            $this->getDefaultRequest()
                ->withQueryParams(
                    [
                        'password' => '1E]U_t"0Xh&}gtTPA`|?'
                    ]
                )
        );
        $responseBody      = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue(true === $responseBody['valid']);
    }

}
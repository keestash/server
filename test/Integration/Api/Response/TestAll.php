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

namespace KST\Integration\Api\Response;

use Keestash\Api\Response\ErrorResponse;
use Keestash\Api\Response\ImageResponse;
use Keestash\Api\Response\JsonResponse;
use Keestash\Api\Response\NotFoundResponse;
use Keestash\Api\Response\OkResponse;
use KSP\Api\IResponse;
use KST\Integration\TestCase;

class TestAll extends TestCase {

    public function testErrorResponse(): void {
        $this->assertTrue(IResponse::INTERNAL_SERVER_ERROR === (new ErrorResponse())->getStatusCode());
    }

    public function testInvalidImageResponse(): void {
        $this->assertTrue(IResponse::NOT_FOUND === (new ImageResponse('/tmp/blabla', ''))->getStatusCode());
    }

    public function testValidImageResponse(): void {
        $imageResponse = new ImageResponse(__DIR__ . '/../../../asset/favicon.png', 'png');
        $this->assertTrue(IResponse::OK === $imageResponse->getStatusCode());
        $this->assertTrue('png' === $imageResponse->getHeader(IResponse::HEADER_CONTENT_TYPE)[0]);
    }

    public function testJsonResponse(): void {
        $data            = [
            'test'   => TestAll::class
            , 'time' => time()
        ];
        $imageResponse   = new JsonResponse($data, IResponse::OK);
        $decodedResponse = $this->getResponseBody($imageResponse);
        $this->assertTrue(IResponse::OK === $imageResponse->getStatusCode());
        $this->assertTrue(
            0 === count(array_diff($data, $decodedResponse))
        );
    }

    public function testNotFound(): void {
        $this->assertTrue(IResponse::NOT_FOUND === (new NotFoundResponse())->getStatusCode());
    }

    public function testOk(): void {
        $this->assertTrue(IResponse::OK === (new OkResponse())->getStatusCode());
    }

}
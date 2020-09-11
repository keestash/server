<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KST\Api\Response;

use doganoo\PHPUtil\HTTP\Code;
use Keestash\Api\Response\DefaultResponse;
use KSP\Api\IResponse;
use KST\TestCase;

class DefaultResponseTest extends TestCase {

    public function testDefaults() {

        $response = new DefaultResponse();

        $this->assertTrue($response->getCode() === Code::OK);
        $this->assertTrue($response->getMessage() === "[]");
    }

    public function testWithCustomValues() {
        $response = new DefaultResponse();
        $response->setCode(Code::BAD_REQUEST);

        $messages = [
            IResponse::RESPONSE_CODE_OK       => [
                "message"    => "operation successfull"
                , "duration" => 0.0000001
            ]
            , IResponse::RESPONSE_CODE_NOT_OK => [
                "negative_message" => "operation not successfull"
                , "costs"          => 345024.213
                , "currency"       => "EUR"
            ]
        ];
        foreach ($messages as $code => $message) {
            $response->addMessage($code, $message);
        }

        $this->assertTrue($response->getCode() === Code::BAD_REQUEST);
        $this->assertTrue($response->getMessage() === json_encode($messages));
    }

}

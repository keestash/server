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

namespace KSA\PasswordManager\Api\Generate;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Generate implements RequestHandlerInterface {

    private IPasswordService $passwordService;

    public function __construct(IPasswordService $passwordService) {
        $this->passwordService = $passwordService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $length       = (string) $request->getAttribute("length");
        $upperCase    = (string) $request->getAttribute("upperCase");
        $lowerCase    = (string) $request->getAttribute("lowerCase");
        $digit        = (string) $request->getAttribute("digit");
        $specialChars = (string) $request->getAttribute("specialChars");

        $valid = $this->validParameters(
            $length
            , $upperCase
            , $lowerCase
            , $digit
            , $specialChars
        );

        if (false === $valid) {
            return new JsonResponse(
                [
                    "message" => "invalid parameters"
                ]
                , IResponse::NOT_ACCEPTABLE
            );
        }

        $password = $this->passwordService->generatePassword(
            (int) $length
            , $upperCase === "true"
            , $lowerCase === "true"
            , $digit === "true"
            , $specialChars === "true"
        );

        return new JsonResponse(
            [
                "password" => $password
            ]
            , IResponse::OK
        );
    }

    private function validParameters(
        string   $length
        , string $upperCase
        , string $lowerCase
        , string $digit
        , string $specialChars
    ): bool {
        $validOptions = [
            "true"
            , "false"
        ];

        $fields = [
            $upperCase
            , $lowerCase
            , $digit
            , $specialChars
        ];

        foreach ($fields as $field) {
            if (false === in_array($field, $validOptions, true)) {
                return false;
            }
        }

        if (false === is_numeric($length)) return false;

        return true;
    }

}

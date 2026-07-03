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

namespace KSA\PasswordManager\Api\Node\Credential\Generate;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

#[OA\Get(
    path: '/password_manager/generate_password/{length}/{upperCase}/{lowerCase}/{digit}/{specialChars}',
    operationId: 'passwordManagerGeneratePassword',
    summary: 'Generate a random password',
    tags: ['Password Manager - Credentials'],
    parameters: [
        new OA\Parameter(name: 'length', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        new OA\Parameter(name: 'upperCase', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['true', 'false'])),
        new OA\Parameter(name: 'lowerCase', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['true', 'false'])),
        new OA\Parameter(name: 'digit', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['true', 'false'])),
        new OA\Parameter(name: 'specialChars', in: 'path', required: true, schema: new OA\Schema(type: 'string', enum: ['true', 'false'])),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Generated password',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'password', type: 'string'),
                ]
            )
        ),
    ],
    security: [['tokenAuth' => [], 'userAuth' => []]]
)]
final readonly class Generate implements RequestHandlerInterface {

    public function __construct(
        private IPasswordService  $passwordService
        , private LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $length       = (string) $request->getAttribute("length");
        $upperCase    = (string) $request->getAttribute("upperCase");
        $lowerCase    = (string) $request->getAttribute("lowerCase");
        $digit        = (string) $request->getAttribute("digit");
        $specialChars = (string) $request->getAttribute("specialChars");

        $this->logger->debug('generate password attributes',
            [
                'length'       => $length,
                'upperCase'    => $upperCase,
                'lowerCase'    => $lowerCase,
                'digit'        => $digit,
                'specialChars' => $specialChars
            ]
        );
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

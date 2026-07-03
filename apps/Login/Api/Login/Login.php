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

namespace KSA\Login\Api\Login;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Api\Version\IVersion;
use KSP\Core\Service\Metric\ICollectorService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

#[OA\Post(
    path: '/login/submit',
    operationId: 'loginSubmit',
    summary: 'Authenticate a user and obtain session tokens',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['user', 'password'],
            properties: [
                new OA\Property(property: 'user', type: 'string', description: 'Username'),
                new OA\Property(property: 'password', type: 'string', description: 'Password', format: 'password'),
            ]
        )
    ),
    tags: ['Authentication'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Login successful',
            headers: [
                new OA\Header(header: 'x-keestash-token', description: 'Session token', schema: new OA\Schema(type: 'string')),
                new OA\Header(header: 'x-keestash-user', description: 'User hash', schema: new OA\Schema(type: 'string')),
            ],
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'settings', type: 'object'),
                    new OA\Property(property: 'user', type: 'object'),
                    new OA\Property(property: 'derivation', type: 'string', format: 'byte', description: 'Base64-encoded derivation'),
                    new OA\Property(property: 'key', type: 'string', format: 'byte', description: 'Base64-encoded key'),
                ]
            )
        ),
        new OA\Response(
            response: 401,
            description: 'Authentication failed',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'responseCode', type: 'integer'),
                ]
            )
        ),
    ]
)]
readonly final class Login implements RequestHandlerInterface {

    public function __construct(
        private Alpha               $alpha
        , private ICollectorService $collector
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IVersion $version */
        $version = $request->getAttribute(IVersion::class);

        $response = match ($version->getVersion()) {
            1, 2 => $this->alpha->handle($request),
            default => new JsonResponse([], IResponse::NOT_FOUND),
        };

        $this->collector->addCounter(
            $response->getStatusCode() === IResponse::OK
                ? 'loginsuccessfull'
                : 'errorlogin'
        );
        return $response;
    }

}

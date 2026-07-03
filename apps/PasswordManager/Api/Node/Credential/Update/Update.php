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

namespace KSA\PasswordManager\Api\Node\Credential\Update;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Api\Version\IVersion;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Update
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * TODO
 *      handle non existent parameters
 *      handle more fields
 */
#[OA\Post(
    path: '/password_manager/credential/update',
    operationId: 'passwordManagerCredentialUpdate',
    summary: 'Update a credential node',
    requestBody: new OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            required: ['nodeId'],
            properties: [
                new OA\Property(property: 'nodeId', type: 'integer'),
                new OA\Property(property: 'name', type: 'string'),
                new OA\Property(property: 'username', type: 'string', format: 'byte', description: 'Base64-encoded username'),
                new OA\Property(property: 'password', type: 'string', format: 'byte', description: 'Base64-encoded password'),
                new OA\Property(property: 'url', type: 'string', format: 'byte', description: 'Base64-encoded URL'),
            ]
        )
    ),
    tags: ['Password Manager - Credentials'],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Credential updated',
            content: new OA\JsonContent(type: 'object')
        ),
    ],
    security: [['tokenAuth' => [], 'userAuth' => []]]
)]
final readonly class Update implements RequestHandlerInterface {

    public function __construct(private Beta $beta) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IVersion $version */
        $version = $request->getAttribute(IVersion::class);
        return match ($version->getVersion()) {
            1, 2 => $this->beta->handle($request),
            default => new JsonResponse([], IResponse::NOT_FOUND),
        };
    }

}

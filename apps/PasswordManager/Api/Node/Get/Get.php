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

namespace KSA\PasswordManager\Api\Node\Get;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Api\Version\IVersion;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Node
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
#[OA\Get(
    path: '/password_manager/node/get/{node_id}',
    operationId: 'passwordManagerNodeGet',
    summary: 'Get a password manager node by ID',
    tags: ['Password Manager - Nodes'],
    parameters: [
        new OA\Parameter(name: 'node_id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
    ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Node data',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'breadCrumb', type: 'array', items: new OA\Items()),
                    new OA\Property(property: 'node', type: 'object'),
                    new OA\Property(property: 'pwned', type: 'object'),
                ]
            )
        ),
    ],
    security: [['tokenAuth' => [], 'userAuth' => []]]
)]
final readonly class Get implements RequestHandlerInterface {

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

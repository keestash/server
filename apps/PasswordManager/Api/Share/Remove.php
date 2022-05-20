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

namespace KSA\PasswordManager\Api\Share;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private NodeRepository $nodeRepository;

    public function __construct(NodeRepository $nodeRepository) {
        $this->nodeRepository = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $shareId    = $parameters["shareId"] ?? null;

        if (null === $shareId) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $removed = $this->nodeRepository->removeEdge((string) $shareId);

        if (false === $removed) {
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }
        return new JsonResponse([], IResponse::OK);
    }

}

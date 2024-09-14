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

namespace KSA\PasswordManager\Api\Node;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\Node\NodeNotRemovedException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Delete
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Delete implements RequestHandlerInterface {

    public function __construct(
        private readonly IL10N            $translator
        , private readonly NodeRepository $nodeRepository
        , private readonly NodeService    $nodeService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $id         = (int) ($parameters["node_id"] ?? -1);

        try {
            $node = $this->nodeRepository->getNode($id);
        } catch (PasswordManagerException) {
            return new JsonResponse(
                [
                    $this->translator->translate("no node found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        try {
            $this->nodeService->removeNode($node);
        } catch (NodeNotRemovedException|InvalidNodeTypeException) {
            return new JsonResponse(
                []
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [], IResponse::OK
        );
    }

}

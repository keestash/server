<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Exception\Node\NodeNotUpdatedException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Update implements RequestHandlerInterface {

    public function __construct(
        private readonly NodeRepository     $nodeRepository
        , private readonly LoggerInterface  $logger
        , private readonly IActivityService $activityService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = $parameters['node_id'] ?? null;
        $name       = $parameters['name'] ?? null;
        $node       = null;

        if (null === $nodeId || null === $name) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId, 0, 0);
        } catch (PasswordManagerException $exception) {
            $this->logger->warning(
                'no node found'
                , [
                    'exception'    => $exception
                    , 'parameters' => $parameters
                ]
            );
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $node->setName($name);
        $node->setUpdateTs(new DateTimeImmutable());

        try {
            $this->nodeRepository->updateNode($node);
            $this->activityService->insertActivityWithSingleMessage(
                ConfigProvider::APP_ID
                , (string) $node->getId()
                , "updated node"
            );
        } catch (NodeNotUpdatedException $exception) {
            $this->logger->warning(
                'could not update node'
                , [
                    'exception' => $exception
                ]
            );
            return new JsonResponse([], IResponse::NOT_MODIFIED);
        }
        return new JsonResponse([], IResponse::OK);
    }

}
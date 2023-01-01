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

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Exception\Edge\EdgeException;
use KSA\PasswordManager\Exception\Node\NodeNotFoundException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Move
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Move implements RequestHandlerInterface {

    private NodeRepository $nodeRepository;
    private IL10N          $translator;
    private AccessService  $accessService;

    public function __construct(
        IL10N                              $l10n
        , NodeRepository                   $nodeRepository
        , AccessService                    $accessService
        , private readonly LoggerInterface $logger
    ) {
        $this->translator     = $l10n;
        $this->nodeRepository = $nodeRepository;
        $this->accessService  = $accessService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $nodeId       = $parameters["node_id"] ?? null;
        $targetNodeId = $parameters["target_node_id"] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
            /** @var Folder $targetNode */
            $targetNode = $this->nodeRepository->getNode((int) $targetNodeId);
        } catch (PasswordManagerException $exception) {
            $this->logger->info('unknown node id', ['exception' => $exception, 'parameters' => $parameters]);
            return new JsonResponse(
                []
                , IResponse::NOT_FOUND
            );
        }

        if (false === $this->accessService->hasAccess($targetNode, $token->getUser())) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("target does not exist")
                ]
                , IResponse::FORBIDDEN);
        }

        if (false === ($targetNode instanceof Folder)) {
            return new JsonResponse(
                [
                    'message' => 'target or parent is not a node'
                ]
                , IResponse::BAD_REQUEST
            );
        }

        // we consider moving nodes around as an update
        $node->setUpdateTs(new DateTimeImmutable());

        try {
            $this->nodeRepository->move(
                $node
                , $targetNode
            );
        } catch (EdgeException|NodeNotFoundException $exception) {
            $this->logger->error(
                'edge not moved'
                , [
                    'exception' => $exception
                    , 'node'    => $node
                    , 'target'  => $targetNode
                ]
            );
            return new JsonResponse(
                [
                    "message" => "could not move node"
                ]
                , IResponse::NOT_MODIFIED
            );
        }

        return new JsonResponse(
            [
                "message" => "moved node"
            ]
            , IResponse::OK
        );
    }

}

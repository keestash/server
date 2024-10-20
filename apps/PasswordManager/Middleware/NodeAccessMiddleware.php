<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Middleware;

use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\HTTP\IResponseService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class NodeAccessMiddleware implements MiddlewareInterface {

    public function __construct(
        private AccessService      $accessService
        , private NodeRepository   $nodeRepository
        , private LoggerInterface  $logger
        , private IResponseService $responseService
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if (true === $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC)) {
            return $handler->handle($request);
        }

        $nodeIds = [];
        $nodeIds = $this->extractNodeIds($request->getAttributes(), $nodeIds);

        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        switch ($request->getMethod()) {
            case IVerb::GET;
                $nodeIds = $this->extractNodeIds($request->getQueryParams(), $nodeIds);
                break;
            case IVerb::PUT:
            case IVerb::DELETE:
            case IVerb::POST;
                $nodeIds = $this->extractNodeIds((array) $request->getParsedBody(), $nodeIds);
                $nodeIds = $this->extractNodeIds((array) json_decode((string) $request->getBody(), true, JSON_THROW_ON_ERROR), $nodeIds);
                break;
        }

        foreach ($nodeIds as $nodeId) {
            $node = null;
            try {
                $node = $this->nodeRepository->getNode((int) $nodeId, 0, 0);
            } catch (PasswordManagerException $exception) {
                $this->logger->debug('no node found', ['nodeId' => $nodeId, 'exception' => $exception]);
                continue;
            }

            if (false === $this->accessService->hasAccess($node, $token->getUser())) {
                return new JsonResponse(
                    [
                        'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_ACCESS_UNAUTHORIZED)
                    ]
                    , IResponse::UNAUTHORIZED
                );
            }
        }

        return $handler->handle($request);
    }

    private function extractNodeIds(array $bag, array $result): array {
        $result[] = $bag['node_id'] ?? null;
        $result[] = $bag['nodeId'] ?? null;
        $result[] = $bag['node']['id'] ?? null;
        $result[] = $bag['parentNodeId'] ?? null;
        $result[] = $bag['parent_node_id'] ?? null;
        return $result;
    }

}

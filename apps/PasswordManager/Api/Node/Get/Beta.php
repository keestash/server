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


namespace KSA\PasswordManager\Api\Node\Get;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Navigation\DefaultEntry;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class Beta {

    public function __construct(
        private NodeRepository     $nodeRepository
        , private LoggerInterface  $logger
        , private NodeService      $nodeService
        , private IResponseService $responseService
        , private IActivityService $activityService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token  = $request->getAttribute(IToken::class);
        $rootId = $request->getAttribute("node_id");

        if (null === $rootId) {
            return new JsonResponse(
                ["no node id given. Could not retrieve data"]
                , IResponse::BAD_REQUEST
            );
        }

        if (false === $this->nodeService->isValidNodeId((string) $rootId)) {
            $this->logger->info('invalid node id', ['nodeId' => $rootId]);
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_INVALID_NODE_ID)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        try {
            $root   = $this->prepareNode($rootId, $token);
            $result = [];
            /** @var Edge $edge */
            foreach ($root->getEdges() as $edge) {
                $result[] = [
                    'id'       => $edge->getNode()->getId(),
                    'name'     => $edge->getNode()->getName(),
                    'type'     => $edge->getNode()->getType(),
                    'userName' => base64_encode($edge->getNode() instanceof Credential ? $edge->getNode()->getUserName()->getEncrypted() : '')
                ];
                $result[] = [
                    'id'       => $edge->getNode()->getId()+1,
                    'name'     => $edge->getNode()->getName(),
                    'type'     => $edge->getNode()->getType(),
                    'userName' => base64_encode($edge->getNode() instanceof Credential ? $edge->getNode()->getUserName()->getEncrypted() : '')
                ];
            }
        } catch (PasswordManagerException|InvalidNodeTypeException $exception) {
            $this->logger->error($exception->getMessage());
            return new JsonResponse(
                ['no data found']
                , IResponse::NOT_FOUND
            );
        }

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $root->getId()
            , sprintf('read by %s', $token->getUser()->getName())
        );

        return new JsonResponse(
            [
                "node" => $result
            ]
            , IResponse::OK
        );
    }

    /**
     * @param string $id
     * @param IToken $token
     * @return Node
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    private function prepareNode(string $id, IToken $token): Node {

        // base case 1: we are requesting a regular node.
        //      select and return
        if (true === is_numeric($id)) {
            return $this->nodeRepository->getNode(
                (int) $id
                , 0
                , 1
            );
        }

        $root = $this->nodeRepository->getRootForUser(
            $token->getUser()
            , 0
            , 2
        );

        // base case 2: we are requesting the root. No need to do the following stuff
        if (Node::ROOT === $id) {
            return $root;
        }

        // regular cases: we are requesting one of the defaults. Check!
        // @codeCoverageIgnoreStart
        switch ($id) {
            case DefaultEntry::DEFAULT_ENTRY_RECENTLY_MODIFIED:

                $edges = $root->getEdges()->toArray();

                usort(
                    $edges
                    , static function (Edge $current, Edge $next): int {
                    /** @var DateTime $currentTs */
                    $currentTs = null !== $current->getNode()->getUpdateTs()
                        ? $current->getNode()->getUpdateTs()
                        : new DateTime();
                    /** @var DateTime $nextTs */
                    $nextTs = null !== $next->getNode()->getUpdateTs()
                        ? $next->getNode()->getUpdateTs()
                        : new DateTime();
                    return $nextTs->getTimestamp() - $currentTs->getTimestamp();
                });

                $edgez = new ArrayList();
                $edgez->addAllArray($edges);
                $root->setEdges($edgez);
                break;

            case DefaultEntry::DEFAULT_ENTRY_SHARED_WITH_ME:

                $newEdges = new ArrayList();
                /** @var Edge $edge */
                foreach ($root->getEdges() as $edge) {

                    $node = $edge->getNode();
                    if (true === $node->isSharedTo($token->getUser())) {
                        $newEdges->add($node);
                    }
                }
                $root->setEdges($newEdges);
                break;
            default:
                throw new PasswordManagerException('unknown operation ' . $id);
        }
        // @codeCoverageIgnoreEnd

        return $root;
    }

}
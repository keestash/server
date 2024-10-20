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

use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Exception\InvalidNodeTypeException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\HTTP\IResponseService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class Beta {

    public function __construct(
        private NodeRepository             $nodeRepository
        , private LoggerInterface          $logger
        , private NodeService              $nodeService
        , private IResponseService         $responseService
        , private IActivityService         $activityService
        , private BreadCrumbService        $breadCrumbService
        , private PwnedPasswordsRepository $pwnedPasswordsRepository
        , private PwnedBreachesRepository  $pwnedBreachesRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {


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

            $root = $this->prepareNode($rootId, $token);
            $this->activityService->insertActivityWithSingleMessage(
                ConfigProvider::APP_ID
                , (string) $root->getId()
                , sprintf('read by %s', $token->getUser()->getName())
            );

            return new JsonResponse(
                [
                    "breadCrumb" => $this->breadCrumbService->getBreadCrumbs($root, $token->getUser()),
                    "node"       => $root,
                    "pwned"      => [
                        'passwords' => $this->pwnedPasswordsRepository->getPwnedByNode($root)->toArray(),
                        'breaches'  => $this->pwnedBreachesRepository->getPwnedByNode($root)->toArray()
                    ],
                ]
                , IResponse::OK
            );
        } catch (PasswordManagerException $e) {
            $this->logger->error('error with getting passwords', ['e' => $e]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }
    }

    /**
     * @param string $id
     * @param IToken $token
     * @return Node
     * @throws PasswordManagerException
     * @throws InvalidNodeTypeException
     */
    private function prepareNode(string $id, IToken $token): Node {
        if (true === is_numeric($id)) {
            return $this->nodeRepository->getNode(
                (int) $id
                , 0
                , 1
            );
        }

        return $this->nodeRepository->getRootForUser(
            $token->getUser()
            , 0
            , 2
        );
    }

}

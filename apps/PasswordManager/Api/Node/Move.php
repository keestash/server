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

use DateTime;
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

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
        IL10N            $l10n
        , NodeRepository $nodeRepository
        , AccessService  $accessService
    ) {
        $this->translator     = $l10n;
        $this->nodeRepository = $nodeRepository;
        $this->accessService  = $accessService;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $nodeId       = $parameters["id"] ?? null;
        $targetNodeId = $parameters["target_node_id"] ?? null;
        $parentNodeId = $parameters["parent_node_id"] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("node does not exist")
                ],
                IResponse::NOT_FOUND
            );
        }

        if (false === $this->accessService->hasAccess($node, $token->getUser())) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("node does not exist")
                ]
            );
        }

        try {
            /** @var Folder $targetNode */
            $targetNode = $this->nodeRepository->getNode((int) $targetNodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("target does not exist")
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (
            $targetNode->isSharedTo($token->getUser())
            || false === $this->accessService->hasAccess($targetNode, $token->getUser())
        ) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("target does not exist")
                ]
            );
        }

        try {
            /** @var Folder $parentNode */
            $parentNode = $this->nodeRepository->getNode((int) $parentNodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("parent does not exist")
                ]
                , IResponse::NOT_FOUND
            );

        }

        if (false === $this->accessService->hasAccess($targetNode, $token->getUser())) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("parent does not exist")
                ]
            );
        }

        // we consider moving nodes around as an update
        $node->setUpdateTs(new DateTime());

        $moved = $this->nodeRepository->move(
            $node
            , $parentNode
            , $targetNode
        );

        if (false === $moved) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "could not move node"
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => "moved node"
            ]
        );
    }

}

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
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Share implements RequestHandlerInterface {

    private NodeRepository  $nodeRepository;
    private IUserRepository $userRepository;
    private NodeService     $nodeService;
    private IL10N           $translator;

    public function __construct(
        IL10N             $l10n
        , NodeRepository  $nodeRepository
        , IUserRepository $userRepository
        , NodeService     $nodeService
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->userRepository = $userRepository;
        $this->nodeService    = $nodeService;
        $this->translator     = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = $parameters['node_id'] ?? null;
        $userId     = $parameters['user_id_to_share'] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $nodeId) {
            return new JsonResponse([
                "message" => "no node found"
            ], IResponse::BAD_REQUEST
            );
        }

        if (null === $userId) {
            return new JsonResponse(
                [
                    "message" => "no user found"
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $shareable = $this->nodeService->isShareable((int) $nodeId, (string) $userId);

        if (false === $shareable) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("can not share with owner / already shared")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        // TODO not optimal, but we need to check anyhow
        $node = $this->nodeRepository->getNode((int) $nodeId);

        if ($node->getUser()->getId() !== $token->getUser()->getId()) {
            throw new PasswordManagerException();
        }

        $edge = $this->nodeRepository->addEdge(
            $this->nodeService->prepareSharedEdge(
                (int) $nodeId
                , (string) $userId
            )
        );

        if ($edge->getId() === 0) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("could not insert")
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        $node  = $this->nodeRepository->getNode((int) $nodeId);
        $user  = $this->userRepository->getUserById((string) $userId);
        $share = $node->getShareByUser($user);

        return new JsonResponse(
            [
                "share" => $share
            ]
            , IResponse::OK
        );
    }

}

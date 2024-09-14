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

namespace KSA\PasswordManager\Api\Node\Share\Regular;

use Exception;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Share implements RequestHandlerInterface {

    public function __construct(
        private NodeRepository    $nodeRepository
        , private IUserRepository $userRepository
        , private NodeService     $nodeService
        , private ShareService    $shareService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            $parameters = (array) $request->getParsedBody();
            $nodeId     = $parameters['node_id'] ?? null;
            $userId     = $parameters['user_id_to_share'] ?? null;

            if (null === $nodeId || null === $userId) {
                return new JsonResponse([], IResponse::BAD_REQUEST);
            }

            $shareable = $this->shareService->isShareable((int) $nodeId, (string) $userId);

            if (false === $shareable) {
                return new JsonResponse(
                    []
                    , IResponse::BAD_REQUEST
                );
            }

            $this->nodeRepository->addEdge(
                $this->nodeService->prepareSharedEdge(
                    (int) $nodeId
                    , (string) $userId
                )
            );
            $node  = $this->nodeRepository->getNode((int) $nodeId);
            $user  = $this->userRepository->getUserById((string) $userId);
            $share = $node->getShareByUser($user);

            return new JsonResponse(
                [
                    "share" => $share
                ]
                , IResponse::OK
            );
        } catch (PasswordManagerException) {
            return new JsonResponse(
                []
                , IResponse::INTERNAL_SERVER_ERROR
            );
        } catch (Exception) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

    }

}

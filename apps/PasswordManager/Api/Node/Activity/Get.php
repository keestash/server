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

namespace KSA\PasswordManager\Api\Node\Activity;

use Keestash\Api\Response\JsonResponse;
use KSA\Activity\Exception\ActivityNotFoundException;
use KSA\Activity\Repository\ActivityRepository;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Get implements RequestHandlerInterface {

    public function __construct(
        private readonly ActivityRepository $activityRepository
        , private readonly NodeRepository   $nodeRepository
        , private readonly AccessService    $accessService
        , private readonly LoggerInterface  $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            $appId        = $request->getAttribute('appId');
            $referenceKey = $request->getAttribute('referenceKey');
            $node         = $this->nodeRepository->getNode((int) $referenceKey, 0, 0);

            /** @var IToken $token */
            $token = $request->getAttribute(IToken::class);
            if (false === $this->accessService->hasAccess($node, $token->getUser())) {
                $this->logger->warning(
                    'user is not allowed to retrieve activity'
                    , [
                        'appId'          => $appId
                        , 'referenceKey' => $referenceKey
                        , 'nodeId'       => $node->getId()
                        , 'userId'       => $token->getUser()->getId()
                    ]
                );
                return new JsonResponse([], IResponse::NOT_ALLOWED);
            }

            $list = $this->activityRepository->getAll((string) $appId, (string) $referenceKey);
        } catch (ActivityNotFoundException $e) {
            $this->logger->debug(
                'activity not found'
                , [
                    'appId'          => $appId
                    , 'referenceKey' => $referenceKey
                    , 'exception'    => $e
                ]
            );
            return new JsonResponse(['activityList' => []], IResponse::NOT_FOUND);
        } catch (PasswordManagerException $e) {
            $this->logger->error(
                'error while retrieving activity'
                , [
                    'appId'          => $appId
                    , 'referenceKey' => $referenceKey
                    , 'exception'    => $e
                ]
            );
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        return new JsonResponse(['activityList' => $list->toArray()], IResponse::OK);
    }

}
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

namespace KSA\PasswordManager\Api\Node\Share\Public;

use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Entity\Share\NullShare;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use KSP\Core\Service\HTTP\IResponseService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

/**
 * Class PublicShare
 * @package KSA\PasswordManager\Api\Share
 */
final readonly class PublicSharePassword implements RequestHandlerInterface {

    public function __construct(
        private NodeRepository          $nodeRepository
        , private ShareService          $shareService
        , private PublicShareRepository $shareRepository
        , private LoggerInterface       $logger
        , private IResponseService      $responseService
        , private AccessService         $accessService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            $parameters = (array) $request->getParsedBody();
            $nodeId     = $parameters["node_id"] ?? null;
            $expireDate = $parameters["expire_date"] ?? null;
            $expireDate = new \DateTime($expireDate);

            if (null === $nodeId) {
                return new JsonResponse(
                    [
                        "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_SHARE_PUBLIC_INVALID_PAYLOAD)
                    ],
                    IResponse::BAD_REQUEST
                );
            }

            $node        = $this->nodeRepository->getNode((int) $nodeId);
            $publicShare = $this->shareService->createPublicShare($node, $expireDate, (string) Uuid::uuid4());
            $node->setPublicShare($publicShare);

            $share = $this->shareRepository->getShareByNode($node);

            if (!($share instanceof NullShare) && false === $this->shareService->isExpired($share)) {
                return new JsonResponse([], IResponse::CONFLICT);
            }

            $node = $this->shareRepository->shareNode($node);

            return new JsonResponse(
                [
                    "share" => $node->getPublicShare()
                ]
                , IResponse::OK
            );
        } catch (PasswordManagerException $exception) {
            $this->logger->error('error public share', ['e' => $exception]);
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_NODE_SHARE_PUBLIC_NOT_FOUND)
                ],
                IResponse::NOT_FOUND
            );
        }
    }

}

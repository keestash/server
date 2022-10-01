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

use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class PublicShare
 * @package KSA\PasswordManager\Api\Share
 */
class PublicShare implements RequestHandlerInterface {

    private NodeRepository        $nodeRepository;
    private ShareService          $shareService;
    private PublicShareRepository $shareRepository;

    public function __construct(
        NodeRepository          $nodeRepository
        , ShareService          $shareService
        , PublicShareRepository $shareRepository
    ) {
        $this->nodeRepository  = $nodeRepository;
        $this->shareService    = $shareService;
        $this->shareRepository = $shareRepository;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $nodeId     = $parameters["node_id"] ?? null;

        if (null === $nodeId) {
            return new JsonResponse([
                "message" => "no node found"
            ], IResponse::BAD_REQUEST);
        }

        try {
            $node = $this->nodeRepository->getNode((int) $nodeId);
        } catch (PasswordManagerException $exception) {
            return new JsonResponse(
                [
                    "message" => "no node found 2"
                ]
                , IResponse::NOT_FOUND
            );
        }

        $publicShare = $this->shareService->createPublicShare($node);
        $node->setPublicShare($publicShare);

        $share = $this->shareRepository->getShareByNode($node);

        if (null !== $share && false === $share->isExpired()) {
            // TODO unshare
        }

        $node = $this->shareRepository->shareNode($node);

        return new JsonResponse([
            "share" => $node->getPublicShare()
        ], IResponse::OK);
    }

}

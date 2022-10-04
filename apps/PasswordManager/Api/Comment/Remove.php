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

namespace KSA\PasswordManager\Api\Comment;

use DateTime;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private IL10N             $translator;
    private NodeRepository    $nodeRepository;

    public function __construct(
        IL10N               $l10n
        , CommentRepository $commentRepository
        , NodeRepository    $nodeRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->translator        = $l10n;
        $this->nodeRepository    = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $commentId  = $parameters["commentId"] ?? null;

        if (null === $commentId) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no subject given")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $node    = $this->commentRepository->getNodeByCommentId((int) $commentId);
        $removed = $this->commentRepository->remove((int) $commentId);

        if (false === $removed) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("could not remove node")
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        $node->setUpdateTs(new DateTime());
        $this->nodeRepository->updateCredential($node);

        return new JsonResponse(
            [
                "message"     => $this->translator->translate("node removed")
                , "commentId" => $commentId
            ]
            , IResponse::OK
        );

    }

}

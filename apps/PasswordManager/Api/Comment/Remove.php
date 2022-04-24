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
use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private IL10N             $translator;
    private AccessService     $accessService;
    private NodeRepository    $nodeRepository;

    public function __construct(
        IL10N               $l10n
        , CommentRepository $commentRepository
        , AccessService     $accessService
        , NodeRepository    $nodeRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->translator        = $l10n;
        $this->accessService     = $accessService;
        $this->nodeRepository    = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $commentId  = $parameters["commentId"] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $commentId) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no subject given")
                ]
            );

        }

        $node = $this->commentRepository->getNodeByCommentId((int) $commentId);
        if (false === $this->accessService->hasAccess($node, $token->getUser())) {
            return new JsonResponse(
                ''
                , IResponse::UNAUTHORIZED
            );
        }

        $removed = $this->commentRepository->remove((int) $commentId);

        if (false === $removed) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not remove node")
                ]
            );

        }

        $node->setUpdateTs(new DateTime());
        $this->nodeRepository->updateCredential($node);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"     => $this->translator->translate("node removed")
                , "commentId" => $commentId
            ]
        );

    }

}

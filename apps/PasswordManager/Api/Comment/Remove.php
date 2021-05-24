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

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Repository\CommentRepository;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Remove implements RequestHandlerInterface {

    private CommentRepository $commentRepository;
    private IL10N             $translator;

    public function __construct(
        IL10N $l10n
        , CommentRepository $commentRepository
    ) {
        $this->commentRepository = $commentRepository;
        $this->translator        = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);
        $commentId  = $parameters["commentId"] ?? null;

        if (null === $commentId) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no subject given")
                ]
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

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"     => $this->translator->translate("node removed")
                , "commentId" => $commentId
            ]
        );

    }

}

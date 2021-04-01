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

use Keestash\Api\AbstractApi;

use KSA\PasswordManager\Repository\CommentRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Remove extends AbstractApi {

    /** @var array $parameters */
    private $parameters;

    /** @var CommentRepository */
    private $commentRepository;

    public function __construct(
        IL10N $l10n
        , CommentRepository $commentRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->commentRepository = $commentRepository;
    }

    public function onCreate(array $parameters): void {
        $this->parameters = $parameters;
    }

    public function create(): void {
        $subjectId = $this->parameters["subject_id"] ?? null;

        if (null === $subjectId) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no subject given")
                ]
            );
            return;

        }


        $removed = $this->commentRepository->remove((string) $subjectId);

        if (false === $removed) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not remove node")
                ]
            );
            return;

        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("node removed")
            ]
        );

    }

    public function afterCreate(): void {

    }

}

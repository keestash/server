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

use Keestash\Api\AbstractApi;

use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

class Remove extends AbstractApi {

    private NodeRepository $nodeRepository;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository = $nodeRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {

        $shareId = $this->getParameter("shareId", null);

        if (null === $shareId) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no edge found")
                ]
            );

            return;
        }

        $removed = $this->nodeRepository->removeEdge((string) $shareId);

        if (false === $removed) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not remove edge")
                ]
            );
            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "shareId" => $shareId
            ]
        );

    }

    public function afterCreate(): void {

    }

}

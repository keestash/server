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

namespace KSA\PasswordManager\Api\Node;

use Keestash\Api\AbstractApi;

use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class Delete
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Delete extends AbstractApi {

    private NodeService    $nodeService;
    private NodeRepository $nodeRepository;

    public function __construct(
        IL10N $l10n
        , NodeService $nodeService
        , NodeRepository $nodeRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeService    = $nodeService;
        $this->nodeRepository = $nodeRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $id   = $this->getParameter("id", (string) 0);
        $type = $this->getParameter("type", "");

        $deletable = $this->nodeService->deletableType($type);
        $node      = $this->nodeRepository->getNode((int) $id);

        if (false === $deletable) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("type $type is not deletable")
                ]
            );

            return;
        }

        if (null === $node || $node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no node found")
                ]
            );

            return;
        }

        $deleted = $this->nodeRepository->remove($node);

        if (false === $deleted) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("error while deleting")
                ]
            );

            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("deleted")
            ]
        );
    }

    public function afterCreate(): void {

    }

}

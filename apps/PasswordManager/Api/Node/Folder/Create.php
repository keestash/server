<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Folder;

use DateTime;
use Keestash\Api\AbstractApi;
use KSA\PasswordManager\Application\Application;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;

use KSP\L10N\IL10N;

/**
 * Class NodeCreate
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Create extends AbstractApi {

    private NodeRepository        $nodeRepository;
    private NodeService           $nodeService;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , NodeService $nodeService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository       = $nodeRepository;
        $this->nodeService          = $nodeService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $title  = $this->getParameter("title");
        $parent = $this->getParameter("parent");

        if (false === $this->isValid($title) || false === $this->isValid($parent)) {

            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no parameters set")
                ]
            );

            return;

        }

        $parentEdge = $this->getParentEdge($parent);

        if (null === $parentEdge || $parentEdge->getUser()->getId() !== $this->getToken()->getUser()->getId()) {

            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no parent found")
                ]
            );

            return;

        }

        $folder = new Folder();
        $folder->setUser($this->getToken()->getUser());
        $folder->setName((string) $title);
        $folder->setType(Node::FOLDER);
        $folder->setCreateTs(new DateTime());

        $lastId = $this->nodeRepository->addFolder($folder);

        if (null === $lastId || 0 === $lastId) {
            parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not add")
                ]
            );
            return;
        }

        $folder->setId($lastId);

        $this->nodeRepository->addEdge(
            $this->nodeService->prepareRegularEdge(
                $folder
                , $parentEdge
            )
        );

        parent::createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message"       => $this->getL10N()->translate("success")
                , "folder"      => $folder
                , "parent_edge" => $parentEdge
            ]
        );

    }

    private function isValid($value): bool {
        if (null === $value) return false;
        if ("" === trim($value)) return false;
        return true;
    }

    private function getParentEdge($parent): ?Node {

        if (Application::ROOT_FOLDER === $parent) {
            return $this->nodeRepository->getRootForUser($this->getToken()->getUser());
        }

        return $this->nodeRepository->getNode((int) $parent);
    }

    public function afterCreate(): void {

    }

}

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

use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class Share extends AbstractApi {

    private NodeRepository  $nodeRepository;
    private IUserRepository $userRepository;
    private NodeService     $nodeService;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , IUserRepository $userRepository
        , NodeService $nodeService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository = $nodeRepository;
        $this->userRepository = $userRepository;
        $this->nodeService    = $nodeService;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $nodeId = $this->getParameter('node_id', null);
        $userId = $this->getParameter('user_id_to_share', null);

        if (null === $nodeId) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no node found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        if (null === $userId) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no user found"
                ]
            );
            parent::setResponse($response);
            return;
        }

        $shareable = $this->nodeService->isShareable((int) $nodeId, (string) $userId);

        if (false === $shareable) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("can not share with owner / already shared")
                ]
            );
            parent::setResponse($response);
            return;
        }

        // TODO not optimal, but we need to check anyhow
        $node = $this->nodeRepository->getNode((int) $nodeId);

        if ($node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            throw new PasswordManagerException();
        }

        $insertId = $this->nodeRepository->addEdge(
            $this->nodeService->prepareSharedEdge(
                (int) $nodeId
                , (string) $userId
            )
        );

        if (null === $insertId) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not insert")
                ]
            );
            parent::setResponse($response);
            return;
        }

        $node  = $this->nodeRepository->getNode((int) $nodeId);
        $share = $node->getShareByUser(
            $this->userRepository->getUserById((string) $userId)
        );

        $response = parent::createResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "share" => $share
            ]
        );

        parent::setResponse($response);
    }

    public function afterCreate(): void {

    }

}

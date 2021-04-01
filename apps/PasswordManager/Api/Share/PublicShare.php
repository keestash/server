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
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;

/**
 * Class PublicShare
 * @package KSA\PasswordManager\Api\Share
 */
class PublicShare extends AbstractApi {

    private NodeRepository        $nodeRepository;
    private ShareService          $shareService;
    private PublicShareRepository $shareRepository;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , ShareService $shareService
        , PublicShareRepository $shareRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->nodeRepository  = $nodeRepository;
        $this->shareService    = $shareService;
        $this->shareRepository = $shareRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $nodeId = $this->getParameter("node_id");

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

        $node = $this->nodeRepository->getNode((int) $nodeId);

        if (null === $node || $node->getUser()->getId() !== $this->getToken()->getUser()->getId()) {
            $response = parent::createResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no node found 2"
                ]
            );
            parent::setResponse($response);
            return;
        }

        $publicShare = new \KSA\PasswordManager\Entity\Share\PublicShare();
        $publicShare->setHash($this->shareService->generateSharingHash($node));
        $publicShare->setExpireTs($this->shareService->getDefaultExpireDate());
        $publicShare->setNodeId($node->getId());
        $node->setPublicShare($publicShare);

        $share = $this->shareRepository->getShareByNode($node);

        if (null !== $share && false === $share->isExpired()) {
            // TODO unshare
        }
        $node = $this->shareRepository->shareNode($node);

        if (null === $node) {
            // TODO handle
        }

        $response = parent::createResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "share" => $node->getPublicShare()
            ]
        );
        parent::setResponse($response);
    }

    public function afterCreate(): void {

    }

}

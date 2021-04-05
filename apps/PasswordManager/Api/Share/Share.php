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

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Share implements RequestHandlerInterface {

    private NodeRepository  $nodeRepository;
    private IUserRepository $userRepository;
    private NodeService     $nodeService;
    private IL10N           $translator;

    public function __construct(
        IL10N $l10n
        , NodeRepository $nodeRepository
        , IUserRepository $userRepository
        , NodeService $nodeService
    ) {
        $this->nodeRepository = $nodeRepository;
        $this->userRepository = $userRepository;
        $this->nodeService    = $nodeService;
        $this->translator     = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);
        $nodeId     = $parameters['node_id'] ?? null;
        $userId     = $parameters['user_id_to_share'] ?? null;
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $nodeId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no node found"
                ]
            );
        }

        if (null === $userId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => "no user found"
                ]
            );
        }

        $shareable = $this->nodeService->isShareable((int) $nodeId, (string) $userId);

        if (false === $shareable) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("can not share with owner / already shared")
                ]
            );
        }

        // TODO not optimal, but we need to check anyhow
        $node = $this->nodeRepository->getNode((int) $nodeId);

        if ($node->getUser()->getId() !== $token->getUser()->getId()) {
            throw new PasswordManagerException();
        }

        $insertId = $this->nodeRepository->addEdge(
            $this->nodeService->prepareSharedEdge(
                (int) $nodeId
                , (string) $userId
            )
        );

        if (null === $insertId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not insert")
                ]
            );
        }

        $node  = $this->nodeRepository->getNode((int) $nodeId);
        $share = $node->getShareByUser(
            $this->userRepository->getUserById((string) $userId)
        );

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "share" => $share
            ]
        );
    }

}

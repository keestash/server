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

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\NodeService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Delete
 *
 * @package KSA\PasswordManager\Api\Node
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Delete implements RequestHandlerInterface {

    private NodeService    $nodeService;
    private NodeRepository $nodeRepository;
    private IL10N          $translator;

    public function __construct(
        IL10N $l10n
        , NodeService $nodeService
        , NodeRepository $nodeRepository
    ) {
        $this->translator     = $l10n;
        $this->nodeService    = $nodeService;
        $this->nodeRepository = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string) $request->getBody(), true);
        $id         = $parameters["id"] ?? "0";
        $type       = $parameters["type"] ?? "";
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        $deletable = $this->nodeService->isDeletable($type);
        $node      = $this->nodeRepository->getNode((int) $id);

        if (false === $deletable) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("type $type is not deletable")
                ]
            );
        }

        if (null === $node || $node->getUser()->getId() !== $token->getUser()->getId()) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no node found")
                ]
            );
        }

        $deleted = $this->nodeRepository->remove($node);

        if (false === $deleted) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("error while deleting")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->translator->translate("deleted")
            ]
        );
    }

}

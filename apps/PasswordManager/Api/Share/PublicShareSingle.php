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
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PublicShareSingle implements RequestHandlerInterface {

    private PublicShareRepository $shareRepository;
    private NodeRepository        $nodeRepository;
    private CredentialService     $credentialService;
    private IL10N                 $translator;

    public function __construct(
        IL10N $l10n
        , PublicShareRepository $shareRepository
        , NodeRepository $nodeRepository
        , CredentialService $credentialService
    ) {
        $this->translator        = $l10n;
        $this->shareRepository   = $shareRepository;
        $this->nodeRepository    = $nodeRepository;
        $this->credentialService = $credentialService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = $request->getQueryParams();
        $hash       = $parameters['hash'] ?? null;

        if (null === $hash) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no password found for hash")
                    , "hash"  => $hash
                ]
            );
        }

        $share = $this->shareRepository->getShare($hash);

        if (null === $share || $share->isExpired()) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no share found")
                ]
            );
        }

        /** @var Credential $node */
        $node = $this->nodeRepository->getNode($share->getNodeId(), 0, 1);

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "response_code" => IResponse::RESPONSE_CODE_OK
                , "decrypted"   => $this->credentialService->getDecryptedPassword($node)
            ]
        );


    }

}

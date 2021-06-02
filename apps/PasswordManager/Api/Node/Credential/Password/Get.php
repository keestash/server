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

namespace KSA\PasswordManager\Api\Node\Credential\Password;

use Keestash\Api\Response\LegacyResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

/**
 * Class Credential
 *
 * @package KSA\PasswordManager\Api
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class Get implements RequestHandlerInterface {

    private NodeRepository    $nodeRepository;
    private CredentialService $credentialService;

    public function __construct(
        CredentialService $credentialService
        , NodeRepository $nodeRepository
    ) {
        $this->credentialService = $credentialService;
        $this->nodeRepository    = $nodeRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        /** @var IToken $token */
        $token  = $request->getAttribute(IToken::class);
        $nodeId = (int) $request->getAttribute("id", 0);

        $node = $this->nodeRepository->getNode($nodeId, 1);

        if (null === $node || $node->getUser()->getId() !== $token->getUser()->getId()) {
            return LegacyResponse::fromData(
                404
                , []
                , 404
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , ["response_code" => IResponse::RESPONSE_CODE_OK
               , "decrypted"   => $this->credentialService->getDecryptedPassword($node)]
        );
    }

}

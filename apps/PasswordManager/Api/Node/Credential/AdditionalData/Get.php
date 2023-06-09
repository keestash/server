<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Credential\AdditionalData;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Get implements RequestHandlerInterface {

    public function __construct(
        private readonly AdditionalDataRepository $additionalDataRepository
        , private readonly NodeRepository         $nodeRepository
        , private readonly AccessService          $accessService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $credentialId = (int) $request->getAttribute('credentialId', 0);

        try {
            $node = $this->nodeRepository->getNode($credentialId, 0, 0);
        } catch (PasswordManagerException) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);

        if (false === ($node instanceof Credential)) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (false === $this->accessService->hasAccess($node, $token->getUser())) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        $data = $this->additionalDataRepository->getByNode($node);
        return new JsonResponse(
            [
                'data' => $data->toArray()
            ]
            , IResponse::OK
        );
    }

}
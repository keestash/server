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
use KSA\PasswordManager\Exception\Node\NotFoundException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Service\Node\Credential\AdditionalData\EncryptionService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class GetValue implements RequestHandlerInterface {

    public function __construct(
        private readonly AdditionalDataRepository $additionalDataRepository
        , private readonly NodeRepository         $nodeRepository
        , private readonly AccessService          $accessService
        , private readonly EncryptionService      $encryptionService
        , private readonly LoggerInterface        $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $id = (string) $request->getAttribute('advid', 0);
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $data  = null;

        try {
            $data = $this->additionalDataRepository->getById($id);
            $node = $this->nodeRepository->getNode($data->getNodeId(), 0, 0);
        } catch (NotFoundException|PasswordManagerException $e) {
            $this->logger->warning(
                'additional data or node not found',
                [
                    'activityId' => $id,
                    'nodeId'     => $data?->getNodeId(),
                    'exception'  => $e
                ]
            );
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (false === ($node instanceof Credential)) {
            return new JsonResponse([], IResponse::NOT_ALLOWED);
        }

        if (false === $this->accessService->hasAccess($node, $token->getUser())) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }
        $data = $this->encryptionService->decrypt($data, $node);

        return new JsonResponse(
            [
                'value' => $data->getValue()->getPlain()
            ]
            , IResponse::OK
        );
    }

}
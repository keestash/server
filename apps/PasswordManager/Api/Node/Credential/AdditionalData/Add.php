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

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\AdditionalData;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\Value;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\AdditionalData\EncryptionService;
use KSP\Api\IResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class Add implements RequestHandlerInterface {

    public function __construct(
        private readonly AdditionalDataRepository $additionalDataRepository
        , private readonly NodeRepository         $nodeRepository
        , private readonly EncryptionService      $encryptionService
        , private readonly LoggerInterface        $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parsedBody   = (array) $request->getParsedBody();
        $credentialId = (int) $parsedBody['credentialId'];
        $key          = (string) $parsedBody['key'];
        $value        = (string) $parsedBody['value'];

        try {
            $node = $this->nodeRepository->getNode($credentialId, 0, 0);
        } catch (PasswordManagerException $e) {
            $this->logger->warning('node not found', ['parsedBody' => $parsedBody, 'exception' => $e]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (false === ($node instanceof Credential)) {
            return new JsonResponse([], IResponse::NOT_ALLOWED);
        }

        $additionalData = new AdditionalData(
            Uuid::uuid4()->toString()
            , $key
            , new Value(
                plain: $value,
                encrypted: null
            )
            , $node->getId()
            , new DateTimeImmutable()
        );

        $additionalDataEncrypted = $this->encryptionService->encrypt($additionalData, $node);
        $this->additionalDataRepository->add($additionalDataEncrypted);
        return new JsonResponse(
            [
                'data' => $additionalData
            ]
            , IResponse::CREATED
        );
    }

}
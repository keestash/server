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
use KSA\Activity\Service\IActivityService;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Node\Credential\AdditionalData\AdditionalData;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\Credential\AdditionalData\AdditionalDataRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

final readonly class Add implements RequestHandlerInterface {

    public function __construct(
        private AdditionalDataRepository $additionalDataRepository
        , private NodeRepository         $nodeRepository
        , private LoggerInterface        $logger
        , private IActivityService       $activityService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token        = $request->getAttribute(IToken::class);
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
            , $value
            , $node->getId()
            , new DateTimeImmutable()
        );

        $this->additionalDataRepository->add($additionalData);

        $this->activityService->insertActivityWithSingleMessage(
            ConfigProvider::APP_ID
            , (string) $node->getId()
            , sprintf(
                "additional data added by %s",
                $token->getUser()->getId()
            )
        );

        return new JsonResponse(
            [
                'data' => $additionalData
            ]
            , IResponse::CREATED
        );
    }

}

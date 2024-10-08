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

namespace KSA\PasswordManager\Api\Node\Credential;

use Keestash\Api\Response\ErrorResponse;
use Keestash\Api\Response\NotFoundResponse;
use Keestash\Api\Response\OkResponse;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Get implements RequestHandlerInterface {

    public function __construct(
        private readonly CredentialService $credentialService
        , private readonly NodeRepository  $nodeRepository
        , private readonly LoggerInterface $logger
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $nodeId = (int) $request->getAttribute("node_id", 0);

        try {
            $node = $this->nodeRepository->getNode($nodeId, 0, 0);
        } catch (PasswordManagerException $exception) {
            $this->logger->error($exception->getMessage() . ' ' . $exception->getTraceAsString());
            return new ErrorResponse();
        }

        if (false === $node instanceof Credential) {
            return new NotFoundResponse();
        }

        return new OkResponse(
            [
                "decrypted" => [
                    'userName' => $this->credentialService->getDecryptedUsername($node),
                    'password' => $this->credentialService->getDecryptedPassword($node)
                ]
            ]
        );
    }

}
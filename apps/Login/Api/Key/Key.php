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

namespace KSA\Login\Api\Key;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\IBase64Service;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

readonly final class Key implements RequestHandlerInterface {

    public function __construct(
        private IUserKeyRepository   $userKeyRepository
        , private LoggerInterface    $logger
        , private IDerivationService $derivationService
        , private IBase64Service     $base64Service
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        try {
            /** @var IToken $token */
            $token = $request->getAttribute(IToken::class);
            $user  = $token->getUser();
            $key   = $this->userKeyRepository->getKey($user);
            return new JsonResponse(
                [
                    'key'        => $this->base64Service->encrypt($key->getSecret()),
                    'derivation' => $this->base64Service->encrypt($this->derivationService->derive($user->getPassword()))
                ],
                IResponse::OK
            );
        } catch (KeyNotFoundException $e) {
            $this->logger->error('error retrieving key for user', ['exception' => $e]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }
    }

}
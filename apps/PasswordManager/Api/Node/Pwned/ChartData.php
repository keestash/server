<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\PasswordManager\Api\Node\Pwned;

use Keestash\Api\Response\JsonResponse;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ChartData implements RequestHandlerInterface {

    private PwnedBreachesRepository  $pwnedBreachesRepository;
    private PwnedPasswordsRepository $pwnedPasswordsRepository;
    private NodeRepository           $nodeRepository;
    private ILogger                  $logger;

    public function __construct(
        PwnedBreachesRepository    $pwnedBreachesRepository
        , PwnedPasswordsRepository $pwnedPasswordsRepository
        , NodeRepository           $nodeRepository
        , ILogger                  $logger
    ) {
        $this->pwnedBreachesRepository  = $pwnedBreachesRepository;
        $this->pwnedPasswordsRepository = $pwnedPasswordsRepository;
        $this->nodeRepository           = $nodeRepository;
        $this->logger                   = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken|null $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $token) {
            return new JsonResponse(['user not found'], IResponse::NOT_FOUND);
        }

        $root = $this->nodeRepository->getRootForUser($token->getUser());

        $passwords = $this->pwnedPasswordsRepository->getPwnedByNode($root, 1);
        $breaches  = $this->pwnedBreachesRepository->getPwnedByNode($root);
        return new JsonResponse(
            [
                'passwords'   => [
                    'amount' => $passwords->size()
                ]
                , 'breaches'  => [
                'amount' => $breaches->size()
            ],
                'totalAmount' => $breaches->size() + $passwords->size()
            ]
            , IResponse::OK
        );
    }

}
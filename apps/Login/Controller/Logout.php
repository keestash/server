<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\Login\Controller;

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Manager\SessionManager\SessionManager;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Logout implements RequestHandlerInterface {

    private ITokenRepository $tokenRepository;
    private SessionManager   $sessionManager;

    public function __construct(
        ITokenRepository $tokenRepository
        , SessionManager $sessionManager
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->sessionManager  = $sessionManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token   = $request->getAttribute(IToken::class);
        $removed = $this->tokenRepository->remove($token);
        $this->sessionManager->killAll();

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "logged_out" => true === $removed
            ]
        );
    }

}
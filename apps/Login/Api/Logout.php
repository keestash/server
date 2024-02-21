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

namespace KSA\Login\Api;

use Keestash\Api\Response\OkResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Metric\ICollectorService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class Logout implements RequestHandlerInterface {

    public function __construct(
        private ITokenRepository        $tokenRepository
        , private IDerivationRepository $derivationRepository
        , private ICollectorService     $collectorService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $this->tokenRepository->remove($token);
        $this->derivationRepository->clear($token->getUser());
        $this->collectorService->addCounter('logout');
        return new OkResponse([]);
    }

}

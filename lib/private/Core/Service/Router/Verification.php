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

namespace Keestash\Core\Service\Router;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\Token\ITokenRepository;

class Verification {

    /** @var ITokenRepository $tokenManager */
    private $tokenManager = null;
    /** @var HashTable $userHashes */
    private $userHashes = null;

    public function __construct(
        ITokenRepository $tokenManager
        , ?HashTable $userHashes
    ) {
        $this->tokenManager = $tokenManager;
        $this->userHashes   = $userHashes;
    }

    public function verifyToken(array $parameters): ?IToken {

        if (null === $this->userHashes) return null;

        $tokenString = $parameters[APIRouter::FIELD_NAME_TOKEN] ?? null;
        $userHash    = $parameters[APIRouter::FIELD_NAME_USER_HASH] ?? null;

        if (null === $tokenString) return null;
        if (null === $userHash) return null;

        if (false === $this->userHashes->containsKey($userHash)) return null;
        $token = $this->tokenManager->getByHash((string) $tokenString);

        if (null === $token) return null;
        if ($token->getValue() !== $tokenString) return null;
        if (true === $token->expired()) return null;

        return $token;
    }

}
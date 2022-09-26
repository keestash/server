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

use Keestash\Exception\UserNotFoundException;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;

class Verification {

    public const FIELD_NAME_USER_HASH = "x-keestash-user";
    public const FIELD_NAME_TOKEN     = "x-keestash-token";

    private ITokenRepository $tokenRepository;
    private IUserRepository  $userRepository;

    public function __construct(
        ITokenRepository  $tokenRepository
        , IUserRepository $userRepository
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->userRepository  = $userRepository;
    }

    public function verifyToken(array $parameters): ?IToken {
        try {

            $tokenString = $parameters[Verification::FIELD_NAME_TOKEN] ?? null;
            $userHash    = $parameters[Verification::FIELD_NAME_USER_HASH] ?? null;

            if (null === $userHash) return null;
            if (null === $tokenString) return null;

            $this->userRepository->getUserByHash($userHash);
            $token = $this->tokenRepository->getByHash((string) $tokenString);

            if (null === $token) return null;
            if ($token->getValue() !== $tokenString) return null;
            if (true === $token->expired()) return null;

            return $token;
        } catch (UserNotFoundException $exception) {
            return null;
        }
    }

}

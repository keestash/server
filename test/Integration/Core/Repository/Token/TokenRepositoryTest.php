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

namespace KST\Integration\Core\Repository\Token;

use DateTimeImmutable;
use Keestash\Core\DTO\Token\Token;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KST\TestCase;
use Ramsey\Uuid\Uuid;

class TokenRepositoryTest extends TestCase {

    public function testAddAndDelete(): void {
        /** @var ITokenRepository $tokenRepository */
        $tokenRepository = $this->getService(ITokenRepository::class);

        $token = new Token();
        $token->setCreateTs(new DateTimeImmutable());
        $token->setName((string) Uuid::uuid4());
        $token->setValue((string) Uuid::uuid4());
        $token->setUser($this->getUser());
        $token = $tokenRepository->add($token);

        $this->assertTrue($token instanceof IToken);
        $token = $tokenRepository->remove($token);
        $this->assertTrue($token instanceof IToken);
    }

    public function testAddAndGetByHash(): void {
        /** @var ITokenRepository $tokenRepository */
        $tokenRepository = $this->getService(ITokenRepository::class);

        $token = new Token();
        $token->setCreateTs(new DateTimeImmutable());
        $token->setName((string) Uuid::uuid4());
        $token->setValue((string) Uuid::uuid4());
        $token->setUser($this->getUser());
        $token    = $tokenRepository->add($token);
        $newToken = $tokenRepository->getByValue($token->getValue());
        $this->assertTrue($newToken instanceof IToken);
        $this->assertTrue($newToken->getId() === $token->getId());
    }

    public function testAddAndGetByHashAndRemoveForUser(): void {
        /** @var ITokenRepository $tokenRepository */
        $tokenRepository = $this->getService(ITokenRepository::class);

        $token = new Token();
        $token->setCreateTs(new DateTimeImmutable());
        $token->setName((string) Uuid::uuid4());
        $token->setValue((string) Uuid::uuid4());
        $token->setUser($this->getUser());
        $token = $tokenRepository->add($token);

        $this->assertTrue($token instanceof IToken);
        $newToken = $tokenRepository->getByValue($token->getValue());
        $this->assertTrue($newToken instanceof IToken);
        $this->assertTrue($newToken->getId() === $token->getId());
        $tokenRepository->removeForUser($this->getUser());
    }

}
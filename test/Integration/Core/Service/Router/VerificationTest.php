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

namespace KST\Integration\Core\Service\Router;

use DateTimeImmutable;
use Keestash\Core\DTO\Token\Token;
use Keestash\Core\Service\Router\VerificationService;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Router\IVerificationService;
use KST\Integration\TestCase;

class VerificationTest extends TestCase {

    private IVerificationService $verificationService;

    protected function setUp(): void {
        parent::setUp();
        $this->verificationService = $this->getService(IVerificationService::class);
    }

    public function testVerify(): void {
        /** @var ITokenRepository $tokenRepository */
        $tokenRepository = $this->getService(ITokenRepository::class);

        $token = new Token();
        $token->setCreateTs(new DateTimeImmutable());
        $token->setName(VerificationTest::class);
        $token->setValue(md5((string) time()));
        $token->setUser($this->getUser());
        $token          = $tokenRepository->add($token);
        $retrievedToken = $this->verificationService->verifyToken(
            [
                VerificationService::FIELD_NAME_TOKEN       => $token->getValue()
                , VerificationService::FIELD_NAME_USER_HASH => $token->getUser()->getHash()

            ]
        );

        $this->assertInstanceOf(IToken::class, $retrievedToken);
        $this->assertTrue($token->getId() === $retrievedToken->getId());
        $this->assertTrue($token->getCreateTs()->getTimestamp() === $retrievedToken->getCreateTs()->getTimestamp());
        $this->assertTrue($token->getName() === $retrievedToken->getName());
        $this->assertTrue($token->getUser()->getId() === $retrievedToken->getUser()->getId());
        $this->assertTrue($token->getValue() === $retrievedToken->getValue());
    }

}
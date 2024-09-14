<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\Login\Test\Unit\Service;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\User\NullUser;
use KSA\Login\Service\TokenService;
use KSA\Login\Test\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class TokenServiceTest extends TestCase {

    private TokenService $tokenService;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->tokenService = $this->getServiceManager()->get(TokenService::class);
    }

    public function testGenerateNonDuplicatedTokens(): void {
        $amount         = 50000;
        $hashTable      = new HashTable();
        $duplicateFound = false;
        for ($i = 0; $i < $amount; $i++) {
            // we want to test the generated token,
            // so it is ok to have all the tokens
            // for a single user
            $token = $this->tokenService->generate('myRandomToken', new NullUser());
            if ($hashTable->containsKey($token->getValue())) {
                $duplicateFound = true;
            }
        }

        $this->assertFalse($duplicateFound);
    }

}
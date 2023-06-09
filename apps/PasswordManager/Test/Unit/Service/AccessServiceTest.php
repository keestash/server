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

namespace KSA\PasswordManager\Test\Unit\Service;


use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Service\AccessService;
use KSA\PasswordManager\Test\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class AccessServiceTest extends TestCase {

    public function testHasAccess(): void {
        /** @var AccessService $accessService */
        $accessService = $this->getService(AccessService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $credential = new Credential();
        $credential->setUser($user);
        $this->assertTrue(true === $accessService->hasAccess($credential, $user));
        $this->removeUser($user);
    }


    public function testHasNoAccess(): void {
        /** @var AccessService $accessService */
        $accessService = $this->getService(AccessService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $otherUser = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $credential = new Credential();
        $credential->setUser($otherUser);
        $this->assertTrue(false === $accessService->hasAccess($credential, $user));
        $this->removeUser($user);
    }

}
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

namespace KSA\PasswordManager\Test\Unit\Service\Encryption;

use Keestash\Core\Service\Encryption\Key\KeyService;
use KSA\PasswordManager\Service\Encryption\EncryptionService;
use KSA\PasswordManager\Test\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class EncryptionServiceTest extends TestCase {

    public function testEncrypt(): void {
        $serviceManager = $this->getServiceManager();
        /** @var EncryptionService $encryptionService */
        $encryptionService = $serviceManager->get(EncryptionService::class);

        /** @var KeyService $keyService */
        $keyService = $this->getServiceManager()
            ->get(KeyService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString()
        );

        $raw       = 'thisisaverysecretstring';
        $key       = $keyService->getKey($user);
        $encrypted = $encryptionService->encrypt($key, $raw);
        $this->assertTrue(true === is_string($encrypted));
        $this->assertTrue(strlen($encrypted) > 0);
        $this->assertTrue($raw !== $encrypted);
        $this->assertTrue($encryptionService->decrypt($key, $encrypted) === $raw);

        $this->removeUser($user);
    }

}
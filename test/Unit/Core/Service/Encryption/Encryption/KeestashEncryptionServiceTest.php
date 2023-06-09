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

namespace KST\Unit\Core\Service\Encryption\Encryption;

use DateTimeImmutable;
use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\User\IUser;
use KST\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class KeestashEncryptionServiceTest extends TestCase {

    public function testEncrypAndDecrypt(): void {
        /** @var KeestashEncryptionService $encryptionService */
        $encryptionService = $this->getService(KeestashEncryptionService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $raw          = md5((string) time());
        $encrypted    = $encryptionService->encrypt(
            $this->getCredential($user)
            , $raw
        );
        $decrpytedRaw = $encryptionService->decrypt(
            $this->getCredential($user)
            , $encrypted
        );

        $this->assertTrue($raw === $decrpytedRaw);
    }

    private function getCredential(IUser $user): ICredential {
        $credential = new Credential();
        $credential->setCreateTs(new DateTimeImmutable());
        $credential->setSecret(md5((string) time()));
        $credential->setKeyHolder($user);
        return $credential;
    }

}
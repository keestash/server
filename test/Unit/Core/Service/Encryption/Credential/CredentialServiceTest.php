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

namespace KST\Unit\Core\Service\Encryption\Credential;

use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use KST\Unit\TestCase;
use Ramsey\Uuid\Uuid;

class CredentialServiceTest extends TestCase {

    public function testCreateCredential(): void {
        /** @var ICredentialService $credentialService */
        $credentialService = $this->getService(ICredentialService::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $credential = $credentialService->createCredentialFromDerivation($user);
        $this->assertTrue($credential instanceof ICredential);
        $this->assertTrue($credential->getKeyHolder()->getId() === $user->getId());
    }

}
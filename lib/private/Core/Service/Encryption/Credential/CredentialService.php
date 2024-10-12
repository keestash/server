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

namespace Keestash\Core\Service\Encryption\Credential;

use DateTimeImmutable;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\Encryption\Credential\Credential;
use KSP\Core\DTO\Derivation\IDerivation;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\Credential\ICredentialService;
use Override;
use Ramsey\Uuid\Uuid;

final readonly class CredentialService implements ICredentialService {

    public function __construct(
        private IDerivationService $derivationService
    ) {
    }

    #[Override]
    public function createCredentialFromDerivation(IKeyHolder $keyHolder): ICredential {
        return $this->createCredentialFromCustomDerivation(
            $keyHolder,
            new Derivation(
                Uuid::uuid4()->toString()
                , $keyHolder
                , $this->derivationService->derive($keyHolder->getPassword())
                , new DateTimeImmutable()
            ));
    }

    #[Override]
    public function createCredentialFromCustomDerivation(IKeyHolder $keyHolder, IDerivation $derivation): ICredential {
        $credential = new Credential();
        $credential->setKeyHolder($keyHolder);
        $credential->setSecret($derivation->getDerived());
        $credential->setCreateTs($keyHolder->getCreateTs());
        $credential->setId($keyHolder->getId());
        return $credential;
    }

}

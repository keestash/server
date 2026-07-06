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

namespace Keestash\Core\Service\Encryption\Key;

use DateTimeImmutable;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Exception\KeestashException;
use Keestash\Exception\Key\UnsupportedKeyException;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Encryption\Key\IKeyService;
use Override;

final readonly class KeyService implements IKeyService {

    public function __construct(
        private IUserKeyRepository           $userKeyRepository
        , private IOrganizationKeyRepository $organizationKeyRepository
    ) {
    }

    /**
     * Stores a given key
     *
     * @param IKeyHolder $keyHolder
     * @param IKey       $key
     * @return IKey
     * @throws UnsupportedKeyException
     */
    #[Override]
    public function storeKey(IKeyHolder $keyHolder, IKey $key): IKey {
        if ($keyHolder instanceof IUser) {
            return $this->userKeyRepository->storeKey($keyHolder, $key);
        } else if ($keyHolder instanceof IOrganization) {
            return $this->organizationKeyRepository->storeKey($keyHolder, $key);
        }
        throw new UnsupportedKeyException();
    }

    /**
     * retrieves a given key
     *
     * @param IKeyHolder $keyHolder
     * @return IKey
     * @throws KeestashException
     */
    #[Override]
    public function getKey(IKeyHolder $keyHolder): IKey {
        if ($keyHolder instanceof IUser) {
            return $this->userKeyRepository->getKey($keyHolder);
        } else if ($keyHolder instanceof IOrganization) {
            return $this->organizationKeyRepository->getKey($keyHolder);
        }
        throw new KeestashException('unsupported keyholder');
    }

    /**
     * @param IKeyHolder $keyHolder
     * @param string     $secret
     * @return IKey
     * @throws UnsupportedKeyException
     */
    #[Override]
    public function createAndStoreKey(IKeyHolder $keyHolder, string $secret, string $kdfVersion): IKey {
        $key = new Key();
        $key->setSecret($secret);
        $key->setKdfVersion($kdfVersion);
        $key->setKeyHolder($keyHolder);
        $key->setCreateTs(new DateTimeImmutable());
        return $this->storeKey($keyHolder, $key);
    }

    /**
     * @param IKeyHolder $keyHolder
     * @return void
     * @throws KeestashException
     */
    #[Override]
    public function remove(IKeyHolder $keyHolder): void {
        if ($keyHolder instanceof IUser) {
            $this->userKeyRepository->remove($keyHolder);
            return;
        }
        if ($keyHolder instanceof IOrganization) {
            $this->organizationKeyRepository->remove($keyHolder);
            return;
        }
        throw new UnsupportedKeyException();
    }

}

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

use DateTime;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\Encryption\KeyHolder\IKeyHolder;
use KSP\Core\DTO\Organization\IOrganization;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\Organization\IOrganizationKeyRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Service\Encryption\IEncryptionService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use Ramsey\Uuid\Uuid;

class KeyService implements IKeyService {

    private IUserKeyRepository         $userKeyRepository;
    private IOrganizationKeyRepository $organizationKeyRepository;
    private IEncryptionService         $encryptionService;

    public function __construct(
        IUserKeyRepository $userKeyRepository
        , IEncryptionService $encryptionService
        , IOrganizationKeyRepository $organizationKeyRepository
    ) {
        $this->userKeyRepository         = $userKeyRepository;
        $this->encryptionService         = $encryptionService;
        $this->organizationKeyRepository = $organizationKeyRepository;
    }

    /**
     * Returns an instance of IKey
     *
     * @param ICredential $credential
     * @param IKeyHolder  $keyHolder
     *
     * @return IKey|null
     */
    public function createKey(ICredential $credential, IKeyHolder $keyHolder): ?IKey {
        // Step 1: we create a random secret
        //      This secret consists of a unique id (uuid)
        //      and a hash created out of the user object
        $secret = Uuid::uuid4() . json_encode($keyHolder);
        // Step 2: we encrypt the data with our base encryption
        $secret = $this->encryptionService->encrypt($credential, $secret);
        // Step 3: we add the data to the database

        if ("" === $secret) return null;

        $key = new Key();
        $key->setSecret($secret);
        $key->setKeyHolder($keyHolder);
        $key->setCreateTs(new DateTime());

        return $key;
    }

    /**
     * Stores a given key
     *
     * @param IKeyHolder $keyHolder
     * @param IKey       $key
     * @return bool
     * @throws PasswordManagerException
     */
    public function storeKey(IKeyHolder $keyHolder, IKey $key): bool {
        if ($keyHolder instanceof IUser) {
            return $this->userKeyRepository->storeKey($keyHolder, $key);
        } else if ($keyHolder instanceof IOrganization) {
            return $this->organizationKeyRepository->storeKey($keyHolder, $key);
        }
        throw new PasswordManagerException('unsupported keyholder');
    }

    /**
     * retrieves a given key
     *
     * @param IKeyHolder $keyHolder
     * @return IKey
     * @throws PasswordManagerException
     */
    public function getKey(IKeyHolder $keyHolder): IKey {
        if ($keyHolder instanceof IUser) {
            return $this->userKeyRepository->getKey($keyHolder);
        } else if ($keyHolder instanceof IOrganization) {
            return $this->organizationKeyRepository->getKey($keyHolder);
        }
        throw new PasswordManagerException('unsupported keyholder');
    }

}

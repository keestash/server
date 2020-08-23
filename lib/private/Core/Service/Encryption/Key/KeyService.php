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
use doganoo\PHPUtil\Util\StringUtil;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Repository\EncryptionKey\EncryptionKeyRepository;
use KSP\Core\DTO\Encryption\Credential\ICredential;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Encryption\IEncryptionService;

class KeyService {

    /** @var EncryptionKeyRepository */
    private $encryptionKeyRepository;

    /** @var IEncryptionService */
    private $encryptionService;

    public function __construct(
        EncryptionKeyRepository $encryptionKeyRepository
        , IEncryptionService $encryptionService
    ) {
        $this->encryptionKeyRepository = $encryptionKeyRepository;
        $this->encryptionService       = $encryptionService;
    }

    /**
     * Returns an instance of IKey
     *
     * @param ICredential $credential
     * @param IUser       $user
     *
     * @return IKey|null
     */
    public function createKey(ICredential $credential, IUser $user): ?IKey {
        // Step 1: we create a random secret
        //      This secret consists of a unique id (uuid)
        //      and a hash created out of the user object
        $secret = StringUtil::getUUID() . json_encode($user);
        // Step 2: we encrypt the data with our base encryption
        $secret = $this->encryptionService->encrypt($credential, $secret);
        // Step 3: we add the data to the database

        if ("" === $secret) return null;

        $key = new Key();
        $key->setSecret($secret);
        $key->setOwner($user);
        $key->setCreateTs(new DateTime());

        return $key;
    }

    public function storeKey(IUser $user, IKey $key): bool {
        return $this->encryptionKeyRepository->storeKey($user, $key);
    }

    public function getKey(IUser $user): ?IKey {
        return $this->encryptionKeyRepository->getKey($user);
    }

}

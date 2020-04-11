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

namespace Keestash\Core\Service\User\Key;

use DateTime;
use doganoo\PHPUtil\Util\StringUtil;
use Keestash\Core\DTO\Key;
use Keestash\Core\Encryption\Base\BaseEncryption;
use Keestash\Core\Repository\EncryptionKey\EncryptionKeyRepository;
use KSP\Core\DTO\User\IUser;

class KeyService {

    /** @var EncryptionKeyRepository */
    private $encryptionKeyRepository;

    public function __construct(EncryptionKeyRepository $encryptionKeyRepository) {
        $this->encryptionKeyRepository = $encryptionKeyRepository;
    }

    public function createKey(BaseEncryption $baseEncryption, IUser $user): bool {
        // Step 1: we create a random secret
        //      This secret consists of a unique id (uuid)
        //      and a hash created out of the user object
        $secret = StringUtil::getUUID() . json_encode($user);
        // Step 2: we encrypt the data with our base encryption
        $secret = $baseEncryption->encrypt($secret);
        // Step 3: we add the data to the database

        $key = new Key();
        $key->setValue($secret);
        $key->setCreateTs(new DateTime());

        $added = $this->encryptionKeyRepository->storeKey(
            $user
            , $key
        );

        return (true === $added) && (true === is_string($secret));
    }

}

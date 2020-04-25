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

namespace Keestash\Core\Service\Encryption\ServerKey;

use Keestash;
use Keestash\Core\Service\Encryption\Base\BaseEncryption;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSP\Core\DTO\Encryption\ServerKey;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;

/**
 * Class ServerKeyService
 * @package Keestash\Core\Service\Encryption\ServerKey
 */
class ServerKeyService {

    /** @var IEncryptionKeyRepository */
    private $encryptionKeyRepository;

    public function __construct(IEncryptionKeyRepository $encryptionKeyRepository) {
        $this->encryptionKeyRepository = $encryptionKeyRepository;
    }

    public function getKeyForUser(IUser $user): ServerKey {
        /** @var BaseEncryption $baseEncryption */
        $baseEncryption = Keestash::getServer()->getBaseEncryption($user);
        $key            = $this->encryptionKeyRepository->getKey($user);

        if (null === $key) {
            throw new KeyNotFoundException("could not find key for {$this->user->getId()}");
        }

        return new ServerKey($baseEncryption->decrypt($key->getValue()));
    }

}

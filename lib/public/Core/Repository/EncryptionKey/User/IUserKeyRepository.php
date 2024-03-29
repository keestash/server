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

namespace KSP\Core\Repository\EncryptionKey\User;

use Doctrine\DBAL\Exception;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\IRepository;

interface IUserKeyRepository extends IRepository {

    public function storeKey(IUser $user, IKey $key): IKey;

    public function updateKey(IKey $key): bool;

    /**
     * @param IUser $user
     * @return IKey
     * @throws Exception
     * @throws KeyNotFoundException
     */
    public function getKey(IUser $user): IKey;

    public function remove(IUser $user): bool;

}

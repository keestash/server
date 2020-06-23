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

namespace Keestash\Core\Repository\EncryptionKey;

use DateTime;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;
use PDO;

class EncryptionKeyRepository extends AbstractRepository implements IEncryptionKeyRepository {

    public function storeKey(IUser $user, IKey $key): bool {
        $sql       = "insert into `key` (`value`, `create_ts`) values (:value, :create_ts);";
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return false;

        $createTs = DateTimeUtil::getUnixTimestamp();
        $value    = $key->getValue();
        $statement->bindParam("value", $value);
        $statement->bindParam("create_ts", $createTs);

        $executed = $statement->execute();
        if (false === $executed) return false;
        $lastInsertId = parent::getLastInsertId();

        $sql       = "insert into `user_key` (`user_id`, `key_id`, `create_ts`) values (:user_id, :key_id, :create_ts)";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return false;
        $userId   = $user->getId();
        $createTs = DateTimeUtil::getUnixTimestamp();
        $statement->bindParam("user_id", $userId);
        $statement->bindParam("key_id", $lastInsertId);
        $statement->bindParam("create_ts", $createTs);
        return $statement->execute();
    }

    public function updateKey(IKey $key): bool {
        $sql       = "update `key` set `value` = :key_value where `id` = :id";
        $statement = parent::prepareStatement($sql);
        $value     = $key->getValue();
        $id        = $key->getId();

        $statement->bindParam("id", $id);
        $statement->bindParam("key_value", $value);
        return $statement->execute();
    }

    public function getKey(IUser $user): ?IKey {
        $sql = "select
                        k.`id`
                        , k.`value`
                        , k.`create_ts`
                from `key` k
                    join `user_key` uk
                        on k.`id` = uk.`key_id`
                where uk.`user_id` = :user_id
                ";

        $statement = parent::prepareStatement($sql);
        if (null === $statement) return null;
        $userId = $user->getId();
        $statement->bindParam("user_id", $userId);
        $executed = $statement->execute();
        if (!$executed) return null;
        if ($statement->rowCount() === 0) return null;

        $key = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $key = new Key();
            $key->setId((int) $row[0]);
            $key->setValue($row[1]);
            $dateTime = new DateTime();
            $dateTime->setTimestamp((int) $row[2]);
            $key->setCreateTs($dateTime);
        }

        return $key;
    }


    public function remove(IUser $user): bool {
        $key       = $this->getKey($user);
        $sql       = "DELETE FROM `user_key` WHERE `key_id` = :key_id;";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return false;
        $keyId = $key->getId();
        $statement->bindParam("key_id", $keyId);
        $statement->execute();

        if (true === $this->hasErrors($statement->errorCode())) return false;

        $sql       = "DELETE FROM `key` WHERE `id` = :id;";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return false;
        $keyId = $key->getId();
        $statement->bindParam("id", $keyId);
        $statement->execute();

        return false === $this->hasErrors($statement->errorCode());

    }

}

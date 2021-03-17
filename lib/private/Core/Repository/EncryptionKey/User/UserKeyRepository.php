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

namespace Keestash\Core\Repository\EncryptionKey\User;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Repository\EncryptionKey\KeyRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;

/**
 * Class UserKeyRepository
 * @package Keestash\Core\Repository\EncryptionKey\User
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class UserKeyRepository extends KeyRepository implements IUserKeyRepository {

    private IDateTimeService $dateTimeService;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
        , ILogger $logger
    ) {
        parent::__construct($backend, $dateTimeService, $logger);
        $this->dateTimeService = $dateTimeService;
    }

    public function storeKey(IUser $user, IKey $key): bool {
        $key = $this->_storeKey($key);

        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder = $queryBuilder
            ->insert('user_key')
            ->values(
                [
                    'user_id'     => '?'
                    , 'key_id'    => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $user->getId())
            ->setParameter(1, $key->getId())
            ->setParameter(2, $this->dateTimeService->toYMDHIS($key->getCreateTs()));

        $queryBuilder->execute();

        return null !== $this->getLastInsertId();
    }

    public function updateKey(IKey $key): bool {
        return $this->_update($key);
    }

    public function getKey(IUser $user): ?IKey {
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->select(
            [
                'k.`id`'
                , 'k.`value`'
                , 'k.`create_ts`'
            ]
        )
            ->from('`key`', 'k')
            ->join('k', '`user_key`', 'uk', 'k.`id` = uk.`key_id`')
            ->where('uk.`user_id` = ?')
            ->setParameter(0, $user->getId());

        $result = $queryBuilder->execute();
        $users  = $result->fetchAllNumeric();

        $key = null;
        foreach ($users as $row) {
            $key = new Key();
            $key->setId((int) $row[0]);
            $key->setSecret($row[1]);
            $key->setCreateTs($this->dateTimeService->fromFormat($row[2]));
            $key->setKeyHolder($user);
        }

        return $key;
    }

    public function remove(IUser $user): bool {
        $key          = $this->getKey($user);
        $queryBuilder = $this->getQueryBuilder();
        $queryBuilder->delete('user_key')
            ->where('key_id = ?')
            ->setParameter(0, $key->getId())
            ->execute();
        return $this->_remove($key);
    }

}

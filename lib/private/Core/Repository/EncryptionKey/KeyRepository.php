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

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\Encryption\Credential\Key\Key;
use Keestash\Core\Repository\AbstractRepository;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\ILogger\ILogger;

abstract class KeyRepository extends AbstractRepository {

    private IDateTimeService $dateTimeService;
    private ILogger          $logger;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
        , ILogger $logger
    ) {
        parent::__construct($backend);
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
    }

    /**
     * @param IKey|Key $key
     * @return IKey
     * @throws KeestashException
     * @throws Exception
     */
    protected function _storeKey(IKey $key): IKey {
        $sql       = "insert into `key` (`value`, `create_ts`) values (:value, :create_ts);";
        $statement = parent::prepareStatement($sql);
        if (null === $statement) return false;

        $createTs = $this->dateTimeService->toYMDHIS($key->getCreateTs());
        $value    = $key->getSecret();
        $statement->bindParam("value", $value);
        $statement->bindParam("create_ts", $createTs);
        $statement->execute();

        $id = $this->getLastInsertId();

        $this->logger->debug((string) $id === null);
        if (null === $id) {
            throw new KeestashException('id is not given!! ' . $id);
        }
        $key->setId((int) $id);
        return $key;
    }

    protected function _update(IKey $key): bool {
        $sql       = "update `key` set `value` = :key_value where `id` = :id";
        $statement = parent::prepareStatement($sql);
        $value     = $key->getSecret();
        $id        = $key->getId();

        $statement->bindParam("id", $id);
        $statement->bindParam("key_value", $value);
        return $statement->execute();
    }

    protected function _remove(IKey $key): bool {
        $queryBuilder = $this->getQueryBuilder();
        return 0 !== $queryBuilder->delete('key')
                ->where('id = ?')
                ->setParameter(0, $key->getId())
                ->execute();
    }

}

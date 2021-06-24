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
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\Encryption\Credential\Key\IKey;
use KSP\Core\ILogger\ILogger;

abstract class KeyRepository {

    private IDateTimeService $dateTimeService;
    private ILogger          $logger;
    private IBackend         $backend;

    public function __construct(
        IBackend $backend
        , IDateTimeService $dateTimeService
        , ILogger $logger
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->logger          = $logger;
        $this->backend         = $backend;
    }

    /**
     * @param IKey|Key $key
     * @return IKey
     * @throws KeestashException
     * @throws Exception
     */
    protected function _storeKey(IKey $key): IKey {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`key`")
            ->values(
                [
                    "`value`"       => '?'
                    , "`create_ts`" => '?'
                ]
            )
            ->setParameter(0, $key->getSecret())
            ->setParameter(1, $this->dateTimeService->toYMDHIS($key->getCreateTs()))
            ->execute();

        $id = (int) $this->backend->getConnection()->lastInsertId();

        if (0 === $id) {
            throw new KeestashException('id is not given!! ' . $id);
        }
        $key->setId((int) $id);
        return $key;
    }

    protected function _update(IKey $key): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->update('key')
            ->set('value', '?')
            ->where('id = ?')
            ->setParameter(0, $key->getSecret())
            ->setParameter(1, $key->getId());
        $rowCount     = $queryBuilder->execute();

        if (0 === $rowCount) {
            return false;
        }

        return true;
    }

    protected function _remove(IKey $key): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return 0 !== $queryBuilder->delete('`key`')
                ->where('id = ?')
                ->setParameter(0, $key->getId())
                ->execute();
    }

}

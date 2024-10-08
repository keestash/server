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

namespace Keestash\Core\Backend\SQLBackend;

use Doctrine\DBAL\Connection;
use Keestash;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Service\Config\IConfigService;

class MySQLBackend implements ISQLBackend {

    public function __construct(
        private readonly Connection       $connection
        , private readonly IConfigService $configService
    ) {
        try {
            $this->connect();
        } catch (\Exception) {

        }
    }

    #[\Override]
    public function connect(): bool {
        $this->connection->connect();
        return true;
    }

    #[\Override]
    public function disconnect(): bool {
        $this->connection->close();
        return true;
    }

    #[\Override]
    public function isConnected(): bool {
        return $this->connection->isConnected();
    }

    #[\Override]
    public function getSchemaName(): string {
        return (string) $this->configService->getValue("db_name");
    }

    /**
     * Returns the doctrine connection
     *
     * @return Connection
     */
    #[\Override]
    public function getConnection(): Connection {
        return $this->connection;
    }

    #[\Override]
    public function getTables(): array {
        return $this->connection->createSchemaManager()->listTableNames();
    }

    #[\Override]
    public function startTransaction(): bool {
        if (true === $this->connection->isTransactionActive()) {
            return true;
        }
        return $this->connection->beginTransaction();
    }

    #[\Override]
    public function endTransaction(): bool {
        if (false === $this->connection->isTransactionActive()) {
            return true;
        }
        return $this->connection->commit();
    }

    #[\Override]
    public function rollbackTransaction(): void {
        if (false === $this->connection->isTransactionActive()) {
            return;
        }
        $this->connection->rollBack();
    }

}

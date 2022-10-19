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

namespace Keestash\Core\Backend;

use Doctrine\DBAL\Connection;
use Keestash;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use KSP\Core\Service\Config\IConfigService;

class MySQLBackend implements ISQLBackend {

    private Connection     $connection;
    private IConfigService $configService;

    public function __construct(
        Connection       $connection
        , IConfigService $configService
    ) {
        $this->connection    = $connection;
        $this->configService = $configService;
        try {
            $this->connect();
        } catch (\Exception $exception) {

        }
    }

    public function connect(): bool {
        $this->connection->connect();
        return true;
    }

    public function disconnect(): bool {
        // TODO implement
        return true;
    }

    public function isConnected(): bool {
        return $this->connection->isConnected();
    }

    public function getSchemaName(): string {
        return (string) $this->configService->getValue("db_name");
    }

    /**
     * Returns the doctrine connection
     *
     * @return Connection
     */
    public function getConnection(): Connection {
        return $this->connection;
    }

    public function getTables(): array {
        return $this->connection->createSchemaManager()->listTableNames();
    }

    public function startTransaction(): bool {
        if (true === $this->connection->isTransactionActive()) {
            return true;
        }
        return $this->connection->beginTransaction();
    }

    public function endTransaction(): bool {
        if (false === $this->connection->isTransactionActive()) {
            return true;
        }
        return $this->connection->commit();
    }

}

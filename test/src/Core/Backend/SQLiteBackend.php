<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KST\Service\Core\Backend;

use Doctrine\DBAL\Connection;
use Keestash\Core\Backend\SQLBackend\MySQLBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;

class SQLiteBackend implements ISQLBackend {

    public function __construct(
        private readonly MySQLBackend $backend
    ) {
        $this->backend->getConnection()->executeStatement('PRAGMA foreign_keys = ON;');
    }

    #[\Override]
    public function connect(): bool {
        return $this->backend->connect();
    }

    #[\Override]
    public function disconnect(): bool {
        return $this->backend->disconnect();
    }

    #[\Override]
    public function isConnected(): bool {
        return $this->backend->isConnected();
    }

    #[\Override]
    public function getConnection(): Connection {
        return $this->backend->getConnection();
    }

    #[\Override]
    public function getSchemaName(): string {
        return $this->backend->getSchemaName();
    }

    #[\Override]
    public function getTables(): array {
        return $this->backend->getTables();
    }

    #[\Override]
    public function startTransaction(): bool {
        return $this->backend->startTransaction();
    }

    #[\Override]
    public function endTransaction(): bool {
        return $this->backend->endTransaction();
    }

    #[\Override]
    public function rollbackTransaction(): void {
        $this->backend->rollbackTransaction();
    }

}
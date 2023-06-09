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
use Keestash\Core\Backend\MySQLBackend;
use KSP\Core\Backend\SQLBackend\ISQLBackend;

class SQLiteBackend implements ISQLBackend {

    public function __construct(
        private readonly MySQLBackend $backend
    ) {
        $this->backend->getConnection()->executeStatement('PRAGMA foreign_keys = ON;');
    }

    public function connect(): bool {
        return $this->backend->connect();
    }

    public function disconnect(): bool {
        return $this->backend->disconnect();
    }

    public function isConnected(): bool {
        return $this->backend->isConnected();
    }

    public function getConnection(): Connection {
        return $this->backend->getConnection();
    }

    public function getSchemaName(): string {
        return $this->backend->getSchemaName();
    }

    public function getTables(): array {
        return $this->backend->getTables();
    }

    public function startTransaction(): bool {
        return $this->backend->startTransaction();
    }

    public function endTransaction(): bool {
        return $this->backend->endTransaction();
    }

}
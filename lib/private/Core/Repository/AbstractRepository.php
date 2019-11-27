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

namespace Keestash\Core\Repository;

use Doctrine\DBAL\Connection;
use KSP\Core\Backend\IBackend;
use KSP\Core\Repository\IRepository;
use PDO;
use PDOStatement;

/**
 * Class AbstractRepository
 * @package Keestash\Core\Repository
 */
class AbstractRepository implements IRepository {

    private $backend = null;
    /** @var PDO $connection */
    private $connection = null;

    public function __construct(IBackend $backend) {
        $this->backend = $backend;
        $this->connect();
        $this->connection = $this->backend->getConnection();
    }

    protected function connect(): bool {
        return $this->backend->connect();
    }

    protected function getLastInsertId(?string $name = null): ?string {
        return $this->connection->lastInsertId($name);
    }

    protected function query(string $sql, array $parameters = []): ?bool {
        $statement = $this->prepareStatement($sql);
        if (null === $statement) return null;

        foreach ($parameters as $key => $value) {
            if (false === is_string($key)) continue;
            $statement->bindParam($key, $value);
        }

        return $statement->execute();
    }

    protected function prepareStatement(string $statement): ?PDOStatement {
        if (false === $this->backend->isConnected()) return null;
        return $this->connection->prepare($statement);
    }

    protected function getSingle(string $sql, array $parameters = []): ?array {
        $statement = $this->prepareStatement($sql);
        if (null === $statement) return null;

        foreach ($parameters as $key => $value) {
            if (false === is_string($key)) continue;
            $statement->bindParam($key, $value);
        }

        $executed = $statement->execute();
        if (false === $executed) return null;
        if (0 === $statement->rowCount()) return null;

        $row = $statement->fetch(PDO::FETCH_BOTH);
        if (0 === count($row)) return null;

        return $row;
    }

}
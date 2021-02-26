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

use Doctrine\DBAL\Query\QueryBuilder;
use KSP\Core\Backend\IBackend;
use KSP\Core\Repository\IRepository;
use PDO;
use PDOStatement;

/**
 * Class AbstractRepository
 *
 * @package Keestash\Core\Repository
 */
class AbstractRepository implements IRepository {

    private IBackend $backend;
    private ?PDO     $connection;

    public function __construct(IBackend $backend) {
        $this->backend = $backend;
        $this->connect();
        $this->connection = $this->backend->getConnection();
    }

    protected function connect(): bool {
        return $this->backend->connect();
    }

    protected function getQueryBuilder(): QueryBuilder {
        return $this->backend->getDoctrineConnection()->createQueryBuilder();
    }

    protected function getDoctrineLastInsertId(): ?string {
        return $this->backend->getDoctrineConnection()->lastInsertId();
    }

    /**
     * @param string|null $name
     * @return string|null
     * @deprecated
     */
    protected function getLastInsertId(?string $name = null): ?string {
        return $this->connection->lastInsertId($name);
    }

    /**
     * @param string $sql
     * @param array  $parameters
     * @return bool
     * @deprecated
     */
    protected function query(string $sql, array $parameters = []): bool {
        $statement = $this->prepareStatement($sql);
        if (null === $statement) return false;

        foreach ($parameters as $key => $value) {
            if (false === is_string($key)) continue;
            $statement->bindParam($key, $value);
        }

        $statement->execute();
        return false === $this->hasErrors($statement->errorCode());
    }

    /**
     * @param string $statement
     * @return PDOStatement|null
     * @deprecated
     */
    protected function prepareStatement(string $statement): ?PDOStatement {
        if (false === $this->backend->isConnected()) return null;
        $statement = $this->connection->prepare($statement);
        if ($statement instanceof PDOStatement) return $statement;
        return null;
    }

    /**
     * @param string $errorCode
     * @return bool
     * @deprecated
     */
    protected function hasErrors(string $errorCode): bool {
        return $errorCode !== "00000";
    }

    protected function getSchemaName(): string {
        return $this->backend->getSchemaName();
    }

    /**
     * @param string $sql
     * @return false|int
     * @deprecated
     */
    protected function rawQuery(string $sql) {
        return $this->connection->exec($sql);
    }

}

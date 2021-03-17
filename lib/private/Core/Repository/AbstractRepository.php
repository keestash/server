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

/**
 * Class AbstractRepository
 *
 * @package Keestash\Core\Repository
 */
class AbstractRepository implements IRepository {

    private IBackend $backend;

    public function __construct(IBackend $backend) {
        $this->backend = $backend;
        $this->connect();
    }

    protected function connect(): bool {
        return $this->backend->connect();
    }

    protected function getQueryBuilder(): QueryBuilder {
        return $this->backend->getConnection()->createQueryBuilder();
    }

    protected function getLastInsertId(): ?string {
        return $this->backend->getConnection()->lastInsertId();
    }

    protected function rawQuery(string $query) {
        return $this->backend->getConnection()->executeQuery($query);
    }

    protected function getSchemaName(): string {
        return $this->backend->getSchemaName();
    }

    protected function getTables(): array {
        return $this->backend->getTables();
    }

}

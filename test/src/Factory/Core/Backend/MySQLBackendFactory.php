<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KST\Service\Factory\Core\Backend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Keestash\Core\Backend\MySQLBackend;
use Mockery;

class MySQLBackendFactory {

    public function __invoke() {
        $queryBuilderMock = Mockery::mock(QueryBuilder::class);

        $connectionMock = Mockery::mock(Connection::class);

        $connectionMock->shouldReceive('createQueryBuilder')
            ->times()
            ->andReturn($queryBuilderMock);
        $connectionMock->shouldReceive('connect')
            ->times()
            ->andReturn(true);

        $backendMock = Mockery::mock(MySQLBackend::class);

        $backendMock->shouldReceive('getConnection')
            ->times()
            ->andReturn($connectionMock);

        $backendMock
            ->shouldReceive('connect')
            ->times()
            ->andReturn(true);
        return $backendMock;
    }

}
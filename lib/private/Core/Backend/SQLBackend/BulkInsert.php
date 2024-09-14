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

namespace Keestash\Core\Backend\SQLBackend;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Schema\Identifier;
use KSP\Core\Backend\SQLBackend\IBulkInsert;

class BulkInsert implements IBulkInsert {

    public function __construct(private readonly Connection $connection) {
    }

    /**
     * @param string $table
     * @param array  $data
     * @param array  $types
     * @return void
     * @throws Exception
     */
    #[\Override]
    public function insert(string $table, array $data, array $types = []): void {
        $dataCount = count($data);
        if (0 === $dataCount) {
            return;
        }

        $this->connection->beginTransaction();
        $this->connection->executeStatement(
            $this->createRawSql($table, $data)
            , $this->normalize(($data))
            , $this->getTypes($types, $dataCount)
        );
        $this->connection->commit();
    }

    private function createRawSql(string $table, array $data): string {
        $platform    = $this->connection->getDatabasePlatform();
        $columns     = $this->quote(
            $platform
            , $this->extract($data)
        );
        $columnCount = count($columns);

        $identifier = new Identifier($table);
        return sprintf(
            'INSERT INTO %s %s VALUES %s;',
            $identifier->getQuotedName($platform),
            0 === $columnCount
                ? ''
                : sprintf('(%s)', implode(', ', $columns)),
            $this->toPlaceHolders($columnCount, count($data))
        );
    }

    private function quote(AbstractPlatform $platform, array $columns): array {
        return array_map(
            static fn(string $column): string => (new Identifier($column))->getQuotedName($platform), $columns);
    }

    function extract(array $data): array {
        $dataCount = count($data);
        if (0 === $dataCount) {
            return [];
        }
        $first = reset($data);
        return array_keys($first);
    }

    private function toPlaceHolders(int $columnsLength, int $datasetLength): string {
        $placeholders = sprintf(
            '(%s)'
            , implode(
                ', '
                , array_fill(0, $columnsLength, '?')
            )
        );
        return implode(', ', array_fill(0, $datasetLength, $placeholders));
    }

    private function normalize(array $data): array {
        return array_reduce(
            $data
            , static function (array $flattenedValues, array $dataSet): array {
            return array_merge($flattenedValues, array_values($dataSet));
        }
            , []
        );
    }

    private function getTypes(array $types, int $datasetLength): array {
        $typeCount = count($types);
        if (0 === $typeCount) {
            return [];
        }

        $types = array_values($types);

        $positionalTypes = [];

        for ($idx = 1; $idx <= $datasetLength; $idx++) {
            $positionalTypes = array_merge($positionalTypes, $types);
        }

        return $positionalTypes;
    }

}

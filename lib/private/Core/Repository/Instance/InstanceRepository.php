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

namespace Keestash\Core\Repository\Instance;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Core\DTO\Instance\Repository\Table;
use Keestash\Core\Repository\AbstractRepository;
use PDO;

class InstanceRepository extends AbstractRepository {

    private function getTables(): ArrayList {
        $list      = new ArrayList();
        $sql       = "SHOW TABLES";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return $list;

        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $list->add($row[0]);
        }

        return $list;
    }

    public function dropSchema(bool $includeSchema = false): bool {

        if (true === $includeSchema) {
            return true === $this->query("DROP SCHEMA {$this->getSchemaName()};");
        }

        $ran = true;
        $this->query("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($this->getTables() as $table) {
            $ran = $this->query(" DROP TABLE IF EXISTS `$table`;");
            FileLogger::debug("ran for $table : $ran");
        }
        $this->query("SET FOREIGN_KEY_CHECKS = 1");

        return $ran;
    }

    /**
     * @param string $table
     * @return ArrayList
     * TODO pay credit to https://www.got-it.ai/solutions/sqlquerychat/sql-help/data-query/how-to-find-the-dependencies-of-a-mysql-table-querychat/
     */
    public function getAllDependantTables(string $table): ArrayList {
        $tableList = new ArrayList();
        $sql       = "
        SELECT TABLE_NAME as `DEPENDENCY`, COLUMN_NAME as `COLUMN`, REFERENCED_TABLE_NAME, REFERENCED_COLUMN_NAME
FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
WHERE REFERENCED_TABLE_NAME = :name;
        ";
        $statement = $this->prepareStatement($sql);
        if (null === $statement) return $tableList;
        $statement->bindParam("name", $table);
        $statement->execute();

        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $table = new Table();
            $table->setName($row[0]);
            $table->setColumn($row[1]);
            $table->setReferencedTable($row[2]);
            $table->setReferencedColumn($row[3]);
            $tableList->add($table);
        }

        return $tableList;
    }

    public function rawQuery(string $sql) {
        return parent::rawQuery($sql);
    }

}

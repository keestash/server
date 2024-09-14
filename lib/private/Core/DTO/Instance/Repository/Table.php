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

namespace Keestash\Core\DTO\Instance\Repository;

use KSP\Core\DTO\Instance\Repository\ITable;

class Table implements ITable {

    /** @var string $name */
    private $name;
    /** @var string $column */
    private $column;
    /** @var string $referencedTable */
    private $referencedTable;
    /** @var string $referencedColumn */
    private $referencedColumn;

    /**
     * @return string
     */
    #[\Override]
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getColumn(): string {
        return $this->column;
    }

    /**
     * @param string $column
     */
    public function setColumn(string $column): void {
        $this->column = $column;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getReferencedTable(): string {
        return $this->referencedTable;
    }

    /**
     * @param string $referencedTable
     */
    public function setReferencedTable(string $referencedTable): void {
        $this->referencedTable = $referencedTable;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getReferencedColumn(): string {
        return $this->referencedColumn;
    }

    /**
     * @param string $referencedColumn
     */
    public function setReferencedColumn(string $referencedColumn): void {
        $this->referencedColumn = $referencedColumn;
    }

    /**
     * @inheritDoc
     */
    #[\Override]
    public function jsonSerialize(): array {
        return [
            "name"                => $this->getName()
            , "column"            => $this->getColumn()
            , "referenced_table"  => $this->getReferencedTable()
            , "referenced_column" => $this->getReferencedColumn()
        ];
    }

}

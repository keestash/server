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

namespace KST\Service\ThirdParty\Phinx\Adapter;

use Phinx\Db\Adapter\SQLiteAdapter as ParentAdapter;
use Phinx\Db\Adapter\UnsupportedColumnTypeException;
use Phinx\Db\Table\Column;
use Phinx\Util\Literal;

class SQLiteAdapter extends ParentAdapter {

    protected static $supportedColumnTypes = [
        ParentAdapter::PHINX_TYPE_BIG_INTEGER   => 'biginteger',
        ParentAdapter::PHINX_TYPE_BINARY        => 'binary_blob',
        ParentAdapter::PHINX_TYPE_BLOB          => 'blob',
        ParentAdapter::PHINX_TYPE_BOOLEAN       => 'boolean_integer',
        ParentAdapter::PHINX_TYPE_CHAR          => 'char',
        ParentAdapter::PHINX_TYPE_DATE          => 'date_text',
        ParentAdapter::PHINX_TYPE_DATETIME      => 'datetime_text',
        ParentAdapter::PHINX_TYPE_DECIMAL       => 'real',
        ParentAdapter::PHINX_TYPE_DOUBLE        => 'double',
        ParentAdapter::PHINX_TYPE_ENUM          => 'varchar',
        ParentAdapter::PHINX_TYPE_FLOAT         => 'float',
        ParentAdapter::PHINX_TYPE_INTEGER       => 'integer',
        ParentAdapter::PHINX_TYPE_JSON          => 'json_text',
        ParentAdapter::PHINX_TYPE_JSONB         => 'jsonb_text',
        ParentAdapter::PHINX_TYPE_SMALL_INTEGER => 'smallinteger',
        ParentAdapter::PHINX_TYPE_STRING        => 'varchar',
        ParentAdapter::PHINX_TYPE_TEXT          => 'text',
        ParentAdapter::PHINX_TYPE_TIME          => 'time_text',
        ParentAdapter::PHINX_TYPE_UUID          => 'uuid_text',
        ParentAdapter::PHINX_TYPE_TIMESTAMP     => 'timestamp_text',
        ParentAdapter::PHINX_TYPE_VARBINARY     => 'varbinary_blob',
    ];

    public function getColumnTypes(): array {
        return array_keys(SQLiteAdapter::$supportedColumnTypes);
    }

    public function getSqlType($type, $limit = null) {
        $typeLC = strtolower($type);
        if ($type instanceof Literal) {
            $name = $type;
        } elseif (isset(self::$supportedColumnTypes[$typeLC])) {
            $name = self::$supportedColumnTypes[$typeLC];
        } elseif (in_array($typeLC, self::$unsupportedColumnTypes, true)) {
            throw new UnsupportedColumnTypeException('Column type "' . $type . '" is not supported by SQLite.');
        } else {
            throw new UnsupportedColumnTypeException('Column type "' . $type . '" is not known by SQLite.');
        }

        return ['name' => $name, 'limit' => $limit];
    }

    protected function getColumnSqlDefinition(Column $column): string {
        $column->setUpdate("");
        return parent::getColumnSqlDefinition($column);
    }

}
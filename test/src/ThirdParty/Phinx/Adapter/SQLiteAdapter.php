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

use Phinx\Db\Adapter\AdapterInterface;
use Phinx\Db\Adapter\SQLiteAdapter as ParentAdapter;
use Phinx\Db\Adapter\UnsupportedColumnTypeException;
use Phinx\Db\Table\Column;
use Phinx\Util\Literal;

class SQLiteAdapter extends ParentAdapter {

    protected static $supportedColumnTypes = [
        AdapterInterface::PHINX_TYPE_BIG_INTEGER   => 'biginteger',
        AdapterInterface::PHINX_TYPE_BINARY        => 'binary_blob',
        AdapterInterface::PHINX_TYPE_BLOB          => 'blob',
        AdapterInterface::PHINX_TYPE_BOOLEAN       => 'boolean_integer',
        AdapterInterface::PHINX_TYPE_CHAR          => 'char',
        AdapterInterface::PHINX_TYPE_DATE          => 'date_text',
        AdapterInterface::PHINX_TYPE_DATETIME      => 'datetime_text',
        AdapterInterface::PHINX_TYPE_DECIMAL       => 'real',
        AdapterInterface::PHINX_TYPE_DOUBLE        => 'double',
        AdapterInterface::PHINX_TYPE_ENUM          => 'varchar',
        AdapterInterface::PHINX_TYPE_FLOAT         => 'float',
        AdapterInterface::PHINX_TYPE_INTEGER       => 'integer',
        AdapterInterface::PHINX_TYPE_JSON          => 'json_text',
        AdapterInterface::PHINX_TYPE_JSONB         => 'jsonb_text',
        AdapterInterface::PHINX_TYPE_SMALL_INTEGER => 'smallinteger',
        AdapterInterface::PHINX_TYPE_STRING        => 'varchar',
        AdapterInterface::PHINX_TYPE_TEXT          => 'text',
        AdapterInterface::PHINX_TYPE_TIME          => 'time_text',
        AdapterInterface::PHINX_TYPE_UUID          => 'uuid_text',
        AdapterInterface::PHINX_TYPE_TIMESTAMP     => 'timestamp_text',
        AdapterInterface::PHINX_TYPE_VARBINARY     => 'varbinary_blob',
    ];

    #[\Override]
    public function getColumnTypes(): array {
        return array_keys(SQLiteAdapter::$supportedColumnTypes);
    }

    #[\Override]
    public function getSqlType($type, ?int $limit = null): array {
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

    #[\Override]
    protected function getColumnSqlDefinition(Column $column): string {
        $column->setUpdate("");
        return parent::getColumnSqlDefinition($column);
    }

}
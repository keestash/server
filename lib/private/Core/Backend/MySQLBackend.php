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

namespace Keestash\Core\Backend;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use KSP\Core\Backend\SQLBackend\ISQLBackend;
use PDO;
use PDOException;

class MySQLBackend implements ISQLBackend {

    /** @var PDO|null $pdo */
    private $pdo = null;

    private $schemaName = null;

    private $connected = false;

    public function __construct(string $schemaName) {
        $this->schemaName = $schemaName;
    }

    public function connect(): bool {
        $config = Keestash::getServer()->getConfig();
        try {
            $this->pdo = new PDO(
                "mysql:host={$config->get("db_host")};dbname={$config->get("db_name")}"
                , $config->get("db_user")
                , $config->get("db_password")
                , [PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES ' . $config->get("db_charset")
                   , PDO::ATTR_ERRMODE          => PDO::ERRMODE_EXCEPTION]
            );
            $this->pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, FALSE);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $this->connected = true;
        } catch (PDOException $exception) {
            FileLogger::error($exception->getMessage());
            return false;
        }
        return true;
    }

    public function disconnect(): bool {
        $this->pdo       = null;
        $this->connected = false;
        return true;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function isConnected(): bool {
        return $this->connected;
    }

}
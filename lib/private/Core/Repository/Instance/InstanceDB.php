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

use DateTime;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash;
use PDO;

class InstanceDB {

    public const FIELD_NAME_INSTANCE_HASH = "instance_hash";
    public const FIELD_NAME_INSTANCE_ID   = "instance_id";

    private $path     = null;
    private $database = null;

    public function __construct() {
        $this->path = Keestash::getServer()->getConfigRoot() . "/.instance.sqlite";

        $this->database = new PDO("sqlite:{$this->path}");

        $this->createTable();

    }

    private function createTable(): void {
        $this->database->query(
            'CREATE TABLE IF NOT EXISTS `instance`
                        (
                            `id` INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL
                            , `name` VARCHAR
                            , `value` TEXT
                            , `create_ts` DATETIME
                        )');
    }

    public function addOption(string $name, string $value): bool {

        $option = $this->getOption($name);
        if (null === $option) {

            return $this->insertOption($name, $value);
        }
        return $this->updateOption($name, $value);
    }

    private function insertOption(string $name, string $value): bool {
        $statement = $this->database->prepare('INSERT INTO `instance` (`name`, `value`, `create_ts`) VALUES (:name, :value, :create_ts)');

        $statement->bindValue(':name', $name);
        $statement->bindValue(':value', $value);
        $createTs = new DateTime();
        $statement->bindValue(':create_ts', $createTs->format(DateTimeUtil::MYSQL_DATE_TIME_FORMAT));
        $statement->execute();

        return true;
    }

    public function updateOption(string $name, string $value): bool {
        $statement = $this->database->prepare('UPDATE `instance` SET `name` =:name, `value` = :value, `create_ts` = :creat_ts');

        $createTs = new DateTime();
        $statement->bindValue(':name', $name);
        $statement->bindValue(':value', $value);
        $statement->bindValue(':create_ts', $createTs->format(DateTimeUtil::MYSQL_DATE_TIME_FORMAT));
        $statement->execute();

        return true;
    }

    public function getAll(): ?array {
        if (false === $this->isValid()) return null;

        $statement = $this->database->prepare('SELECT 
                                                        `id`
                                                        , `name`
                                                        , `value`
                                                        , `create_ts`
                                                      FROM `instance`;');
        $statement->execute();

        $array = [];
        while ($row = $statement->fetch(PDO::FETCH_ASSOC)) {
            $array[] = $row;
        }

        if (false === $array) return null;
        return $array;
    }

    public function getOption(string $name): ?string {
        if (false === is_file($this->path)) return null;

        $statement = $this->database->prepare('SELECT 
                                                        `id`
                                                        , `name`
                                                        , `value`
                                                        , `create_ts`
                                                      FROM `instance`
                                                      WHERE `name` = ?;');

        $statement->bindValue(1, $name);
        $statement->execute();
        $array = $statement->fetch(PDO::FETCH_ASSOC);

        if (false === $array) return null;
        return $array['value'] ?? null;
    }

    public function removeOption(string $name): bool {
        if (false === $this->isValid()) return false;
        $sql       = 'DELETE FROM `instance` WHERE `name` = :the_name';
        $statement = $this->database->prepare($sql);
        $statement->bindValue(':the_name', $name);
        FileLogger::debug(json_encode($statement->errorInfo()));
        $statement->execute();
        return $statement->rowCount() > 0;
    }

    public function clear(): bool {
        if (false === $this->isValid()) return false;
        $statement = $this->database->prepare('DELETE FROM `instance`;');
        $statement->execute();
        return true;
    }

    private function isValid(): bool {
        if (true === is_file($this->path)) return true;
        return false;
    }

}
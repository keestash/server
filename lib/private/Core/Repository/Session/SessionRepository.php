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

namespace Keestash\Core\Repository\Session;

use DateTime;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Repository\Session\ISessionRepository;
use PDO;

class SessionRepository extends AbstractRepository implements ISessionRepository {

    public function open(): bool {
        return true;
    }

    public function get(string $id): ?string {
        $sql       = "SELECT `data` FROM `session` WHERE `id` = :id";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return null;
        $statement->bindParam("id", $id);
        $executed = $statement->execute();

        if (false === $executed) return null;
        if (true === $this->hasErrors($statement->errorCode())) return null;

        $row  = $statement->fetch(PDO::FETCH_BOTH);
        $data = $row["data"];
        return (string) $data;
    }

    public function getAll(): ?array {
        $sql       = "SELECT `id`, `data`, `update_ts` FROM `session`;";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return null;
        $executed = $statement->execute();

        if (false === $executed) return null;
        if (true === $this->hasErrors($statement->errorCode())) return null;

        $result = [];
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $result[] = $row;
        }
        return $result;
    }

    public function replace(string $id, string $data): bool {
        $sql       = "REPLACE INTO `session`(`id`, `data`,`update_ts`) VALUES (:id, :the_data, :update_ts)";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return false;
        $updateTs = DateTimeUtil::formatMysqlDateTime(new DateTime());
        $statement->bindParam("id", $id);
        $statement->bindParam("the_data", $data);
        $statement->bindParam("update_ts", $updateTs);

        $executed = $statement->execute();

        return
            true === $executed &&
            false === $this->hasErrors($statement->errorCode());
    }

    public function deleteById(string $id): bool {
        $sql       = "DELETE FROM `session` WHERE `id` = :id";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return false;
        $statement->bindParam("id", $id);
        $executed = $statement->execute();

        return
            true === $executed &&
            false === $this->hasErrors($statement->errorCode());
    }

    public function deleteByLastUpdate(int $maxLifeTime): bool {
        $sql       = "DELETE FROM `session` WHERE `update_ts` = :update_ts";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return false;
        $updateTs = (new DateTime())->getTimestamp() - $maxLifeTime;
        $statement->bindParam("update_ts", $updateTs);
        $executed = $statement->execute();

        return
            true === $executed &&
            false === $this->hasErrors($statement->errorCode());
    }

    public function close(): bool {
        return true;
    }

}
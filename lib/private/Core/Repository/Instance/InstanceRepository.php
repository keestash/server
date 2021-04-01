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

use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;

class InstanceRepository extends AbstractRepository {

    private ILogger $logger;

    public function __construct(
        IBackend $backend
        , ILogger $logger
    ) {
        parent::__construct($backend);
        $this->logger = $logger;
    }

    public function dropSchema(bool $includeSchema = false): bool {

        if (true === $includeSchema) {
            $this->rawQuery("DROP SCHEMA {$this->getSchemaName()};");
            $this->rawQuery("CREATE SCHEMA {$this->getSchemaName()};");
            return true;
        }

        $queries   = [];
        $queries[] = "SET FOREIGN_KEY_CHECKS = 0";
        foreach ($this->getTables() as $table) {
            $this->logger->debug($table);
            $queries[] = "DROP TABLE IF EXISTS `$table`;";
        }
        $queries[] = "SET FOREIGN_KEY_CHECKS = 1";

        $this->logger->debug(implode(";", $queries));
        return $this->rawQuery(implode(";", $queries))
                ->rowCount() > 0;
    }


    public function rawQuery(string $query) {
        return parent::rawQuery($query);
    }

}

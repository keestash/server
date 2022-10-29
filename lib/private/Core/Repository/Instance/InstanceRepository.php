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

use KSP\Core\Backend\IBackend;
use KSP\Core\Repository\Instance\IInstanceRepository;
use Psr\Log\LoggerInterface;

class InstanceRepository implements IInstanceRepository {

    private LoggerInterface  $logger;
    private IBackend $backend;

    public function __construct(
        IBackend  $backend
        , LoggerInterface $logger
    ) {
        $this->logger  = $logger;
        $this->backend = $backend;
    }

    public function dropSchema(bool $includeSchema = false): bool {

        if (true === $includeSchema) {
            $this->execute("DROP SCHEMA {$this->backend->getSchemaName()};");
            $this->execute("CREATE SCHEMA {$this->backend->getSchemaName()};");
            return true;
        }


        $tables = $this->backend->getTables();
        if (0 === count($tables)) {
            return true;
        }

        $queries = [];
        $this->execute("SET FOREIGN_KEY_CHECKS = 0");
        foreach ($tables as $table) {
            $this->logger->debug($table);
            $this->execute("DROP TABLE IF EXISTS `$table`;");
        }
        $this->execute("SET FOREIGN_KEY_CHECKS = 1");

        $this->logger->debug(implode(";", $queries));
        return true;
    }

    public function execute(string $query): void {
        $this->backend->getConnection()->prepare($query)->execute();
    }

}

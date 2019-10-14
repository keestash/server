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

namespace Keestash\Core\System;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Server;

class System {

    private $configCreated = false;

    public function createConfig(): void {
        if (true === $this->configCreated) return;
        $CONFIG = [];
        $map    = new HashTable();
        /** @noinspection PhpIncludeInspection */
        require Keestash::getServer()->getConfigfilePath();

        foreach ($CONFIG as $key => $value) {
            $map->put($key, $value);
        }

        Keestash::getServer()->register(
            Server::CONFIG
            , function () use ($map) {
            return $map;
        });

        $this->configCreated = true;
    }

}
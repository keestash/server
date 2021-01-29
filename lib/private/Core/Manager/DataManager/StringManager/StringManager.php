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

namespace Keestash\Core\Manager\DataManager\StringManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\Manager\DataManager\StringManager\IStringManager;

class StringManager implements IStringManager {

    private HashTable $apps;

    public function __construct() {
        $this->apps = new HashTable();
    }

    public function add(string $appId, string $value): void {
        if (false === $this->apps->containsKey($appId)) {
            $this->apps->put($appId, new ArrayList());
        }
        /** @var ArrayList $strings */
        $strings = $this->apps->get($appId);
        $strings->add($value);
        $this->apps->put($appId, $strings);
    }

    public function toArray(): array {
        $array = [];
        foreach ($this->apps->keySet() as $appId) {
            /** @var ArrayList $strings */
            $strings       = $this->apps->get($appId);
            $array[$appId] = $strings->toArray();
        }
        return $array;
    }

}

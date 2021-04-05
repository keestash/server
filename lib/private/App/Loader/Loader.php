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

namespace Keestash\App\Loader;

use doganoo\PHPAlgorithms\Datastructure\Cache\LRUCache;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use KSP\App\IApp;
use KSP\App\ILoader;

/**
 * Class Loader
 * @package Keestash\App
 */
class Loader implements ILoader {

    private HashTable $apps;
    private LRUCache  $lruAppCache;

    public function __construct() {
        $this->apps        = new HashTable();
        $this->lruAppCache = new LRUCache();
    }

    public function getApps(): HashTable {
        return $this->apps;
    }


    public function getDefaultApp(): ?IApp {
        return $this->lruAppCache->get(
            $this->lruAppCache->last()
        );
    }

    public function hasApp(string $name): bool {
        if (null === $this->apps) return false;
        return $this->apps->containsKey($name);
    }

    public function unloadApp(string $key): bool {
        return true;
    }

}

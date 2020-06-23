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

namespace Keestash\Core\Manager\HookManager;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\PHPUtil\Log\FileLogger;
use KSP\Core\Manager\HookManager\IHookManager;
use KSP\Hook\IHook;

class HookManager implements IHookManager {

    /** @var ArrayList $preHooks */
    private $preHooks = null;
    /** @var ArrayList $postHooks */
    private $postHooks = null;
    /** @var HashTable */
    protected static $hookCache;

    public function __construct() {
        $this->preHooks    = new ArrayList();
        $this->postHooks   = new ArrayList();
        static::$hookCache = new HashTable();
    }

    public function addPre(IHook $hook): void {
        $this->preHooks->add($hook);
    }

    public function addPost(IHook $hook): void {
        $this->postHooks->add($hook);
    }

    public static function cache(string $key, $value): void {
        static::$hookCache->put($key, $value);
    }

    public static function queryCache(string $key) {
        return static::$hookCache->get($key);
    }

    public function executePre(...$parameters): bool {
        /** @var IHook $hook */
        foreach ($this->preHooks as $hook) {
            $hook->performAction($parameters);
        }
        return true;
    }

    public function executePost(...$parameters): bool {
        /** @var IHook $hook */
        foreach ($this->postHooks as $hook) {
            $hook->performAction($parameters);
        }
        return true;
    }

}

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

namespace Keestash\Core\Manager\ActionBarManager;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Exception;
use KSP\Core\Manager\ActionBarManager\IActionBarManager;
use KSP\Core\View\ActionBar\IActionBarBag;

class ActionBarManager implements IActionBarManager {

    private $actionBarBag = null;

    public function __construct() {
        $this->actionBarBag = new HashTable();
    }

    public function add(string $name, IActionBarBag $bag): void {
        $this->actionBarBag->put($name, $bag);
    }

    public function get(string $name): ?IActionBarBag {
        $bag = $this->actionBarBag->get($name);

        if (null === $bag) throw new Exception();
        return $bag;
    }

}
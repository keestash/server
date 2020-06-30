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

namespace Keestash\View\ActionBar\Bag;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Exception;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarBag;

/**
 * Class ActionBarBag
 *
 * @package Keestash\View\ActionBar
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class ActionBarBag implements IActionBarBag {

    /** @var HashTable */
    private $actionBars;

    public function __construct() {
        $this->actionBars = new HashTable();
    }

    public function add(string $name, IActionBar $actionBar): void {
        $this->actionBars->put($name, $actionBar);
    }

    public function get(string $name): ?IActionBar {
        $actionBar = $this->actionBars->get($name);

        if (null === $actionBar) throw new Exception();
        return $actionBar;
    }

    public function getAll(): ArrayList {
        $list = new ArrayList();
        foreach ($this->actionBars->keySet() as $key) {
            $list->add($this->actionBars->get($key));
        }
        return $list;
    }

}

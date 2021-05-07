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

namespace Keestash\View\Navigation;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\View\Navigation\INavigation;
use KSP\Core\View\Navigation\IPart;

/**
 * Class Navigation
 *
 * @deprecated
 */
class Navigation implements INavigation {

    private ArrayList $parts;

    public function __construct() {
        $this->parts = new ArrayList();
    }

    public function addAll(ArrayList $list): bool {
        $added = false;
        foreach ($list as $item) {
            if ($item instanceof IPart) {
                $this->addPart($item);
            }
        }
        return (bool) $added;
    }

    public function addPart(IPart $part): void {
        $this->parts->add($part);
    }

    public function getAll(): ArrayList {
        return $this->parts;
    }

    public function get(int $index): ?IPart {
        return $this->parts->get($index);
    }

}

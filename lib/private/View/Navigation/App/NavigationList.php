<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\View\Navigation\App;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;

/**
 * Class NavigationList
 *
 * @package Keestash\View\Navigation\App
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class NavigationList extends ArrayList {

    public function add($item): bool {
        if ($item instanceof Segment) {
            return parent::add($item);
        }
        return false;
    }

    public function addAll(ArrayList $arrayList): bool {
        if ($arrayList instanceof NavigationList) {
            return parent::addAll($arrayList);
        }
        return false;
    }

    public function addAllArray(array $array): bool {
        return false;
    }

    public function addToIndex(int $index, $item): bool {
        if ($item instanceof Segment) {
            return parent::addToIndex($index, $item);
        }
        return false;
    }

}

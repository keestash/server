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

namespace Keestash\Core\DTO\File;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\DTO\File\IJsonFile;

class FileList extends ArrayList {

    public function add($item): bool {

        if ($item instanceof IJsonFile) {
            return parent::add($item);
        }

        return false;
    }

    public function addAll(ArrayList $arrayList): bool {
        if ($arrayList instanceof FileList) {
            return parent::addAll($arrayList);
        }
        return false;
    }

    public function addAllArray(array $array): bool {
        return false;
    }

    public function addToIndex(int $index, $item): bool {
        if ($item instanceof IJsonFile) {
            return parent::addToIndex($index, $item);
        }
        return false;
    }

}

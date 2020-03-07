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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\View\Navigation\INavigation;
use KSP\Core\View\Navigation\IPart;

/**
 * Class Navigation
 * @deprecated Please use navigationlist and navigatoinpart instead
 */
class Navigation implements INavigation {

    public const ACTION_ATTRIBUTE_PLACEHOLDER             = "placeholder.attribute.action";
    public const ACTION_ATTRIBUTE_DELETE_MODAL_HEADER     = "header.modal.delete.attribute.action";
    public const ACTION_ATTRIBUTE_DELETE_MODAL_CONTENT    = "content.modal.delete.attribute.action";
    public const ACTION_ATTRIBUTE_DELETE_MODAL_ANSWER_NO  = "no.answer.modal.delete.attribute.action";
    public const ACTION_ATTRIBUTE_DELETE_MODAL_ANSWER_YES = "yes.answer.modal.delete.attribute.action";
    public const ACTION_ATTRIBUTE_DETAIL                  = "detail.attribute.action";
    public const ACTION_ATTRIBUTE_URL                     = "url.attribute.action";

    private $parts = null;

    private $actionAttributes = null;

    public function __construct() {
        $this->parts            = new ArrayList();
        $this->actionAttributes = new HashTable();
    }

    public function addActionAtribute(string $name, string $attribute): bool {
        return $this->actionAttributes->put($name, $attribute);
    }

    public function getActionAtribute(string $name) {
        return $this->actionAttributes->get($name);
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
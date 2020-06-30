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

namespace Keestash\View\ActionBar\ActionBar;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarElement;

abstract class ActionBar implements IActionBar {

    /** @var string */
    private $id;
    /** @var string */
    private $description;
    /** @var ArrayList */
    private $elements;

    public function __construct() {
        $this->elements = new ArrayList();
    }

    public function getType(): string {
        return IActionBar::TYPE_PLUS;
    }

    public function addElement(IActionBarElement $element): void {
        $this->elements->add($element);
    }

    public function hasElements(): bool {
        return 0 !== $this->getElements()->length();
    }

    public function getElements(): ArrayList {
        return $this->elements;
    }

    public function getId(): string {
        return $this->id;
    }

    public function setId(string $name): void {
        $this->id = $name;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function setDescription(string $description): void {
        $this->description = $description;
    }

}

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

namespace Keestash\View\ActionBar;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use KSP\Core\View\ActionBar\IActionBar;
use KSP\Core\View\ActionBar\IActionBarElement;

class SettingsActionBar implements IActionBar {

    private $name     = null;
    private $elements = null;

    public function __construct() {
        $this->elements = new ArrayList();
    }

    public function getType(): string {
        return IActionBar::TYPE_SETTINGS;
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

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

}
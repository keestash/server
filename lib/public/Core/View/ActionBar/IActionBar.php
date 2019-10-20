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

namespace KSP\Core\View\ActionBar;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;

interface IActionBar {

    public const TYPE_PLUS     = "icon plus";
    public const TYPE_SETTINGS = "icon settings";

    public function setName(string $name): void;

    public function getName(): string;

    public function getType(): string;

    public function getElements(): ArrayList;

    public function addElement(IActionBarElement $element): void;

    public function hasElements(): bool;

}
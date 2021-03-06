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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;

interface IActionBar {

    public const TYPE_PLUS     = "fa-plus";
    public const TYPE_SETTINGS = "icon settings";
    public const TYPE_KEY      = "fas fa-key";
    public const TYPE_FOLDER   = "far fa-folder";

    public function setId(string $name): void;

    public function getId(): string;

    public function setDescription(string $description): void;

    public function getDescription(): string;

    public function getType(): string;

    public function setType(string $type): void;

    public function getElements(): ArrayList;

    public function addElement(IElement $element): void;

    public function hasElements(): bool;

}

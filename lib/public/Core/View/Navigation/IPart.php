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

namespace KSP\Core\View\Navigation;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;

interface IPart {

    public const LIST_OL                 = "list ol icon";
    public const LIST_UL                 = "list ul icon";
    public const TASKS                   = "tasks icon";
    public const TH_LIST                 = "th list icon";
    public const BARS                    = "bars icon";
    public const VERTICAL_ELLIPSIS       = "ellipsis vertical icon";
    public const FOLDER                  = "folder icon";
    public const FOLDER_OUTLINE          = "folder outline icon";
    public const FOLDER_OPEN             = "folder open icon";
    public const FOLDER_OPEN_OUTLINE     = "folder open outline icon";
    public const TRASH_ALTERNATE_OUTLINE = "trash alternate outline icon";

    public function getId(): int;

    public function getName(): string;

    public function getEntries(): ArrayList;

    public function addEntry(IEntry $entry): void;

    public function size(): int;

    public function getIconClass(): ?string;

    /** @deprecated do not use */
    public function getColorCode(): string;

}
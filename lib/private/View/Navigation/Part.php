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
use KSP\Core\View\Navigation\IEntry;
use KSP\Core\View\Navigation\IPart;

/**
 * Class Part
 *
 * @package    Keestash\View\Navigation
 * @deprecated
 */
class Part implements IPart {

    private int       $id;
    private string    $name;
    private string    $faClass;
    private ArrayList $entries;
    private string    $colorCode;
    private ?string   $iconClass;

    public function __construct() {
        $this->name    = "";
        $this->faClass = "";
        $this->entries = new ArrayList();
    }

    /**
     * @return string
     */
    public function getColorCode(): string {
        return $this->colorCode;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return ArrayList
     */
    public function getEntries(): ArrayList {
        return $this->entries;
    }

    public function addEntry(IEntry $entry): void {
        $this->entries->add($entry);
    }

    public function size(): int {
        return $this->entries->length();
    }

    public function getIconClass(): ?string {
        return $this->iconClass;
    }

}

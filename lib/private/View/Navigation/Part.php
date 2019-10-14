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
use KSP\Core\View\Navigation\IEntry;
use KSP\Core\View\Navigation\IPart;

/**
 * Class Part
 * @package    Keestash\View\Navigation
 * @deprecated Please use navigationlist and navigatoinpart instead
 */
class Part implements IPart {

    private $id;
    private $name;
    private $faClass;
    private $entries;
    private $colorCode;
    private $href;
    private $iconClass;

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
     * @param string $colorCode
     */
    public function setColorCode(string $colorCode): void {
        $this->colorCode = $colorCode;
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
    public function setName(string $name) {
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

    /**
     * @return string
     */
    public function getHref(): ?string {
        return $this->href;
    }

    /**
     * @param string $href
     */
    public function setHref(string $href): void {
        $this->href = $href;
    }

    public function getIconClass(): ?string {
        return $this->iconClass;
    }

    public function setIconClass(?string $iconClass): void {
        $this->iconClass = $iconClass;
    }

}

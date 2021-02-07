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
use KSP\View\Navigation\App\IEntry;
use KSP\View\Navigation\App\ISegment;

class Segment implements ISegment {

    private string    $title;
    private ?string   $id;
    private ArrayList $entries;

    public function __construct(
        string $title = ""
        , ?string $id = null
    ) {
        $this->entries = new ArrayList();
        $this->setTitle($title);
        $this->setId($id);
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void {
        $this->title = $title;
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

    /**
     * @return string|null
     */
    public function getId(): ?string {
        return $this->id;
    }

    /**
     * @param string|null $id
     */
    public function setId(?string $id): void {
        $this->id = $id;
    }

}

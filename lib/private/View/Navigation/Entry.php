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

use DateTime;
use KSP\Core\View\Navigation\IEntry;

class Entry implements IEntry {

    private $id;
    private $name;
    private $order;
    private $faClass;
    private $startDate;
    private $endDate;
    private $favorite;
    private $visible;

    public function getStartDate(): ?DateTime {
        return $this->startDate;
    }

    public function setStartDate(?DateTime $startDate): void {
        $this->startDate = $startDate;
    }

    public function getEndDate(): ?DateTime {
        return $this->endDate;
    }

    public function setEndDate(?DateTime $endDate): void {
        $this->endDate = $endDate;
    }

    public function setFavorite(bool $favorite): void {
        $this->favorite = $favorite;
    }

    public function getId(): string {
        return $this->id;
    }

    public function setId(string $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function compareTo($object): int {
        if ($object instanceof IEntry) {
            if ($this->getOrder() === $object->getOrder()) return 0;
            if ($this->getOrder() < $object->getOrder()) return -1;
            if ($this->getOrder() > $object->getOrder()) return 1;
        }
        return -1;
    }

    public function getOrder(): int {
        return $this->order;
    }

    public function setOrder(int $order): void {
        $this->order = $order;
    }

    public function isFavorite(): bool {
        return $this->favorite;
    }

    public function setVisible(bool $visible): void {
        $this->visible = $visible;
    }

    public function isVisible(): bool {
        return $this->visible;
    }

    public function getFAClass(): ?string {
        return $this->faClass;
    }

    public function setFAClass(string $faClass): void {
        $this->faClass = $faClass;
    }

}

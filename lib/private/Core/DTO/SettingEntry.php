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

namespace Keestash\Core\DTO;

use DateTime;
use DateTimeInterface;
use KSP\Core\View\Navigation\IEntry;

class SettingEntry implements IEntry {

    private string            $id;
    private string            $name;
    private int               $order;
    private string            $faClass;
    private DateTimeInterface $startDate;
    private DateTimeInterface $endDate;

    /**
     * @param object $object
     * @return int
     */
    public function compareTo($object): int {
        if (!$object instanceof SettingEntry) return -1;

        if ($object->getOrder() < $this->getOrder()) return 1;
        if ($object->getOrder() > $this->getOrder()) return -1;
        if ($object->getOrder() === $this->getOrder()) return 0;

        return -1;
    }

    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function getOrder(): int {
        return $this->order;
    }

    /**
     * @param int $order
     */
    public function setOrder(int $order): void {
        $this->order = $order;
    }

    public function getStartDate(): DateTimeInterface {
        return $this->startDate;
    }

    /**
     * @param int|DateTimeInterface $startDate
     */
    public function setStartDate($startDate): void {
        if (is_int($startDate)) {
            $s = new DateTime();
            $s->setTimestamp($startDate);
            $this->startDate = $s;
            return;
        }
        $this->startDate = $startDate;
    }

    public function getEndDate(): DateTimeInterface {
        return $this->endDate;
    }

    /**
     * @param int|DateTimeInterface $endDate
     */
    public function setEndDate($endDate): void {
        if (is_int($endDate)) {
            $s = new DateTime();
            $s->setTimestamp($endDate);
            $this->endDate = $s;
            return;
        }

        $this->endDate = $endDate;
    }

    public function isFavorite(): bool {
        return false;
    }

    public function isVisible(): bool {
        return null !== $this->getFAClass();
    }

    public function getFAClass(): ?string {
        return $this->faClass;
    }

    /**
     * @param string $faClass
     */
    public function setFaClass(string $faClass): void {
        $this->faClass = $faClass;
    }

}
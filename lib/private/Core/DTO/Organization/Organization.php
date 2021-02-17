<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\DTO\Organization;

use DateTimeInterface;
use KSP\Core\DTO\Organization\IOrganization;

class Organization implements IOrganization {

    private int                $id;
    private string             $name;
    private int                $memberCount;
    private DateTimeInterface  $createTs;
    private ?DateTimeInterface $activeTs = null;

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
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @param DateTimeInterface $createTs
     */
    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * @return int
     */
    public function getMemberCount(): int {
        return $this->memberCount;
    }

    /**
     * @param int $memberCount
     */
    public function setMemberCount(int $memberCount): void {
        $this->memberCount = $memberCount;
    }

    /**
     * @return ?DateTimeInterface
     */
    public function getActiveTs(): ?DateTimeInterface {
        return $this->activeTs;
    }

    /**
     * @param ?DateTimeInterface $activeTs
     */
    public function setActiveTs(?DateTimeInterface $activeTs): void {
        $this->activeTs = $activeTs;
    }

    public function jsonSerialize(): array {
        return [
            'id'             => $this->getId()
            , 'name'         => $this->getName()
            , 'member_count' => $this->getMemberCount()
            , 'create_ts'    => $this->getCreateTs()
            , 'active_ts'    => $this->getActiveTs()
        ];
    }

}
<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSP\Core\DTO\Queue;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\Entity\IJsonObject;

interface IMessage extends IJsonObject {

    /**
     * @return string
     */
    public function getId(): string;

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface;

    /**
     * @return int
     */
    public function getPriority(): int;

    /**
     * @return int
     */
    public function getAttempts(): int;

    /**
     * @return DateTimeInterface
     */
    public function getReservedTs(): DateTimeInterface;

    /**
     * @return array
     */
    public function getPayload(): array;

    public function getStamps(): HashTable;

    public function getStamp(string $name): ?IStamp;

}
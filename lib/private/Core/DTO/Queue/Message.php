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

namespace Keestash\Core\DTO\Queue;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IStamp;

abstract class Message implements IMessage {

    private string            $id;
    private DateTimeInterface $createTs;
    private int               $priority;
    private int               $attempts;
    private DateTimeInterface $reservedTs;
    private array             $payload;
    private string            $type;
    private HashTable         $stamps;

    public function __construct() {
        $this->stamps = new HashTable();
    }

    /**
     * @return string
     */
    public function getId(): string {
        return $this->id;
    }

    /**
     * @param string $id
     */
    public function setId(string $id): void {
        $this->id = $id;
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
    public function getPriority(): int {
        return $this->priority;
    }

    /**
     * @param int $priority
     */
    public function setPriority(int $priority): void {
        $this->priority = $priority;
    }

    /**
     * @return int
     */
    public function getAttempts(): int {
        return $this->attempts;
    }

    /**
     * @param int $attempts
     */
    public function setAttempts(int $attempts): void {
        $this->attempts = $attempts;
    }

    /**
     * @return DateTimeInterface
     */
    public function getReservedTs(): DateTimeInterface {
        return $this->reservedTs;
    }

    /**
     * @param DateTimeInterface $reservedTs
     */
    public function setReservedTs(DateTimeInterface $reservedTs): void {
        $this->reservedTs = $reservedTs;
    }

    /**
     * @return array
     */
    public function getPayload(): array {
        return $this->payload;
    }

    /**
     * @param array $payload
     */
    public function setPayload(array $payload): void {
        $this->payload = $payload;
    }

    /**
     * @return string
     */
    public function getType(): string {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getStamps(): HashTable {
        return $this->stamps;
    }

    public function setStamps(HashTable $stamps): void {
        $this->stamps = $stamps;
    }

    public function addStamp(IStamp $stamp): void {
        $this->stamps->add($stamp->getName(), $stamp);
    }

    public function getStamp(string $name): ?IStamp {
        return $this->stamps->get($name);
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array {
        return [
            "id"            => $this->getId()
            , "create_ts"   => $this->getCreateTs()
            , "priority"    => $this->getPriority()
            , "attempts"    => $this->getAttempts()
            , "reserved_ts" => $this->getReservedTs()
            , "payload"     => $this->getPayload()
            , 'type'        => $this->getType()
            , 'stamps'      => $this->getStamps()
        ];
    }

}
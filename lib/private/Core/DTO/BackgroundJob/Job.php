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

namespace Keestash\Core\DTO\BackgroundJob;

use DateTimeInterface;
use KSP\Core\DTO\BackgroundJob\IJob;

class Job implements IJob {

    public const string JOB_TYPE_ONE_TIME = "time.one.type.job";
    public const string JOB_TYPE_REGULAR  = "regular.type.job";

    private int                $id       = 0;
    private string             $name     = '';
    private int                $interval = 0;
    private string             $type     = '';
    private ?DateTimeInterface $lastRun  = null;
    private ?array             $info     = null;
    private DateTimeInterface  $createTs;

    public function getId(): int {
        return $this->id;
    }

    public function setId(int $id): void {
        $this->id = $id;
    }

    public function getName(): string {
        return $this->name;
    }

    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setType(string $type): void {
        $this->type = $type;
    }

    public function getType(): string {
        return $this->type;
    }

    public function setInterval(int $interval): void {
        $this->interval = $interval;
    }

    public function getInterval(): int {
        return $this->interval;
    }

    public function setLastRun(?DateTimeInterface $lastRun): void {
        $this->lastRun = $lastRun;
    }

    public function getLastRun(): ?DateTimeInterface {
        return $this->lastRun;
    }

    public function setInfo(?array $info): void {
        $this->info = $info;
    }

    public function getInfo(): ?array {
        return $this->info;
    }

    public function addInfo(string $key, mixed $info): void {
        $this->info[$key] = $info;
    }

    public function isOneTime(): bool {
        return self::JOB_TYPE_ONE_TIME === $this->getType();
    }

    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

}

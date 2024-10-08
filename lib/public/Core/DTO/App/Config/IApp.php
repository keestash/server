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

namespace KSP\Core\DTO\App\Config;

use DateTimeInterface;
use KSP\Core\DTO\BackgroundJob\IJobList;
use KSP\Core\DTO\Entity\IJsonObject;

interface IApp extends IJsonObject {

    public const string ENABLED_TRUE  = "true";
    public const string ENABLED_FALSE = "false";

    public function getId(): string;

    public function isEnabled(): bool;

    public function getVersion(): int;

    public function getCreateTs(): DateTimeInterface;

    public function getBackgroundJobs(): IJobList;

}

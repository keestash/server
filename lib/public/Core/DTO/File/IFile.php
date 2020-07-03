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

namespace KSP\Core\DTO\File;

use DateTime;
use KSP\Core\DTO\Object\IJsonObject;
use KSP\Core\DTO\User\IUser;

interface IFile extends IJsonObject {

    public function getId(): int;

    public function getName(): string;

    public function getDirectory(): string;

    public function getTemporaryPath(): ?string;

    public function getMimeType(): string;

    public function getHash(): string;

    public function getExtension(): string;

    public function getSize(): int;

    public function getOwner(): IUser;

    public function getFullPath(): string;

    public function getContent(): ?string;

    public function getCreateTs(): DateTime;

}

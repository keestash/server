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
use KSA\PasswordManager\Object\Node;
use KSP\Core\DTO\IUser;
use KSP\Core\DTO\KSObject;

interface IFile extends KSObject {

    public function getId(): int;

    public function getName(): string;

    public function getPath(): string;

    public function getTemporaryPath(): ?string;

    public function getMimeType(): string;

    public function getHash(): string;

    public function getExtension(): string;

    public function getSize(): int;

    public function getOwner(): IUser;

    public function getFullPath(): string;

    public function getNode(): Node;

    public function getContent(): ?string;

    public function getCreateTs(): DateTime;

}
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

namespace KSP\Core\Manager\DataManager;

use Keestash\Core\DTO\File\FileList;
use KSP\Core\DTO\File\IJsonFile;
use KSP\Core\Manager\IManager;

interface IDataManager extends IManager {

    public function __construct(string $appId, ?string $context = null);

    public function store(IJsonFile $file): bool;

    public function storeAll(FileList $fileList): bool;

    public function get(IJsonFile $file): IJsonFile;

    public function getAll(FileList $fileList): FileList;

    public function remove(IJsonFile $file): bool;

    public function removeAll(FileList $fileList): bool;

    public function getPath(): string;

}

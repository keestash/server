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

namespace KSP\Core\Manager\FileManager;

use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\Manager\IManager;
use KSP\Core\Repository\File\IFileRepository;

interface IFileManager extends IManager {

    public const FILE_PERMISSION = 0770;

    public function __construct(IFileRepository $fileRepository);

    public function write(IFile $file): bool;

    public function remove(IFile $file): bool;

    public function verifyFile(IFile $file): bool;

    public function read(IUniformResourceIdentifier $uri): ?IFile;


}
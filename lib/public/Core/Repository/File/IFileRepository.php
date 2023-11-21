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

namespace KSP\Core\Repository\File;

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\File\FileList;
use Keestash\Exception\File\FileNotCreatedException;
use Keestash\Exception\File\FileNotDeletedException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\File\FileNotUpdatedException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\IRepository;

interface IFileRepository extends IRepository {

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotCreatedException
     */
    public function add(IFile $file): IFile;

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotUpdatedException
     */
    public function update(IFile $file): IFile;

    /**
     * @param FileList $files
     * @return FileList
     * @throws FileNotCreatedException
     */
    public function addAll(FileList $files): FileList;

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotDeletedException
     */
    public function remove(IFile $file): IFile;

    /**
     * @param IUser $user
     * @return void
     * @throws FileNotDeletedException
     */
    public function removeForUser(IUser $user): void;

    /**
     * @param FileList $files
     * @return FileList
     * @throws FileNotDeletedException
     */
    public function removeAll(FileList $files): FileList;

    /**
     * @param int $id
     * @return IFile
     * @throws FileNotFoundException
     */
    public function get(int $id): IFile;

    /**
     * @param IUniformResourceIdentifier $uri
     * @return IFile
     * @throws FileNotFoundException
     */
    public function getByUri(IUniformResourceIdentifier $uri): IFile;

    /**
     * @param string $name
     * @return IFile
     * @throws FileNotFoundException
     * @throws UserNotFoundException
     */
    public function getByName(string $name): IFile;

    public function startTransaction(): void;

    public function endTransaction(): void;

    public function rollBack(): void;

}

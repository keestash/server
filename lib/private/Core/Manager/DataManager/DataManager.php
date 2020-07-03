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

namespace Keestash\Core\Manager\DataManager;

use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\File\FileList;
use Keestash\Exception\FolderNotCreatedException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Manager\DataManager\IDataManager;

/**
 * Class DataManager
 * @package Keestash\Core\Manager\DataManager
 */
class DataManager implements IDataManager {

    private $appId   = null;
    private $context = null;
    private $path    = null;

    public function __construct(string $appId, ?string $context = null) {
        $this->appId   = $appId;
        $this->context = $context;
        $this->buildPath();
        $this->createDir($this->path);
    }

    private function buildPath() {
        $path = Keestash::getServer()->getDataRoot() . "/" . $this->appId;

        if (null !== $this->context) {
            $path = $path . "/" . $this->context;
        }
        $this->path = $path;
    }

    public function getPath(): string {
        return $this->path;
    }

    private function createDir(string $path): bool {
        $realPath = realpath($path);
        $isDir    = false !== $realPath && true === is_dir($realPath);

        if (false === $isDir) {
            $dirCreated = mkdir($path, 0777, true);

            if (false === $dirCreated) {
                throw new FolderNotCreatedException();
            }
        }

        return true;
    }

    public function store(IFile $file): bool {

        $isFile = is_file($file->getFullPath());
        if (true === $isFile) {
            $this->remove($file);
        }

        $copied = copy(
            $file->getTemporaryPath()
            , $file->getFullPath()
        );

        return true === $copied;
    }

    public function get(IFile $file): IFile {
        $file   = new File();
        $isFile = is_file($file->getFullPath());
        if (false === $isFile) return $file;
        $file->setContent(file_get_contents($file->getFullPath()));
        return $file;
    }

    public function storeAll(FileList $fileList): bool {
        $storedAll = false;
        /** @var IFile $file */
        foreach ($fileList as $file) {
            $stored    = $this->store($file);
            $storedAll = $storedAll || $stored;
        }

        return $storedAll;

    }

    public function getAll(FileList $fileList): FileList {

        /** @var IFile $file */
        foreach ($fileList as $index => $file) {
            $file = $this->get($file);
            $fileList->addToIndex($index, $file);
        }

        return $fileList;
    }

    public function remove(IFile $file): bool {

        $fullPath   = $file->getFullPath();
        $isFile     = is_file($fullPath);
        $fileExists = is_file($fullPath);

        if (false === $fileExists) {
            FileLogger::debug("File does not exist, aborting with true for unlink file");
            return true;
        }
        if (false === $isFile) {
            FileLogger::debug("$fullPath is not a file");
            return false;
        }
        $removed = @unlink($fullPath);

        if (false === $removed) {
            FileLogger::debug("could not remove file, unlink returned false");
        }
        return $removed;
    }

    public function removeAll(FileList $fileList): bool {
        $removed = false;
        /** @var IFile $file */
        foreach ($fileList as $file) {

            $removed = $this->remove($file);

        }

        return $removed;
    }

}

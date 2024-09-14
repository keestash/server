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

namespace Keestash\Core\Service\Core\Data;

use Keestash;
use Keestash\Core\DTO\File\File;
use Keestash\Exception\File\FileNotDeletedException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\FolderNotCreatedException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Service\Core\Data\IDataService;
use Laminas\Config\Config;

/**
 * Class DataManager
 *
 * @package Keestash\Core\Manager\DataManager
 */
final readonly class DataService implements IDataService {

    private string $path;

    public function __construct(
        private string    $appId
        , private Config  $config
        , private ?string $context = null
    ) {
        $this->buildPath();
        $this->createDir($this->path);
    }

    #[\Override]
    public function getPath(): string {
        return $this->path;
    }

    #[\Override]
    public function store(IFile $file): bool {

        $isFile = is_file($file->getFullPath());
        if (true === $isFile) {
            $this->remove($file);
        }

        $copied = copy(
            (string) $file->getTemporaryPath()
            , $file->getFullPath()
        );

        return true === $copied;
    }

    /**
     * @param IFile $file
     * @return void
     * @throws FileNotDeletedException
     * @throws FileNotFoundException
     */
    #[\Override]
    public function remove(IFile $file): void {
        $fullPath   = $file->getFullPath();
        $isFile     = is_file($fullPath);
        $fileExists = is_file($fullPath);

        if (false === $fileExists) {
            return;
        }

        if (false === $isFile) {
            throw new FileNotFoundException();
        }
        $removed = @unlink($fullPath);


        if (false === $removed) {
            throw new FileNotDeletedException();
        }
    }

    #[\Override]
    public function get(IFile $file): IFile {
        $file   = new File();
        $isFile = is_file($file->getFullPath());
        if (false === $isFile) return $file;
        $file->setContent((string) file_get_contents($file->getFullPath()));
        return $file;
    }

    private function buildPath(): void {
        $path = $this->config->get(Keestash\ConfigProvider::DATA_PATH) . "/" . $this->appId;

        if (null !== $this->context) {
            $path = $path . "/" . $this->context;
        }
        $this->path = $path;
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

}

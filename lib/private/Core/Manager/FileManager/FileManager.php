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

namespace Keestash\Core\Manager\FileManager;

use Keestash;
use Keestash\Core\DTO\URI\URI;
use Keestash\Server;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Repository\File\IFileRepository;

class FileManager implements IFileManager {

    private $fileRepository = null;

    public function __construct(IFileRepository $fileRepository) {
        $this->fileRepository = $fileRepository;
    }

    public function write(IFile $file): bool {

        if (false === $this->verifyFile($file)) return false;

        $put = file_put_contents(
            $file->getFullPath()
            , file_get_contents(
                $file->getFullPath()
            )
        );

        chmod(
            $file->getFullPath()
            , IFileManager::FILE_PERMISSION
        );

        $added = $this->fileRepository->add($file);
        return true === $put && true === $added;

    }

    public function verifyFile(IFile $file): bool {

        if ($file->getSize() <= 0) return false;

        $allowedImages = Keestash::getServer()->query(Server::ALLOWED_IMAGE_MIME_TYPES);
        $f             = finfo_open();
        $content       = file_get_contents($file->getFullPath());
        $mimeType      = finfo_buffer(
            $f
            , $content
            , FILEINFO_MIME_TYPE
        );

        if (!in_array($mimeType, $allowedImages)) return false;

        return true;

    }

    public function read(IUniformResourceIdentifier $uri): ?IFile {
        $path = $uri->getIdentifier();
        if (false === is_file($path)) return null;

        $file = $this->fileRepository->getByUri($uri);
        return $file;
    }

    public function remove(IFile $file): bool {
        $path = realpath($file->getFullPath());

        if (false === $path) return false;
        $uri = new URI(); // TODO make better
        $uri->setIdentifier($path);;
        $file = $this->fileRepository->getByUri($uri);
        $unlinked = unlink($path);
        $removed  = $this->fileRepository->remove($file);

        return true === $unlinked && true === $removed;
    }

}
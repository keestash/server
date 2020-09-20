<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Service\File\Upload;

use DateTime;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash\Core\DTO\File\File;
use Keestash\Core\Service\Config\IniConfigService;
use KSP\Core\DTO\File\IFile as ICoreFile;
use KSP\Core\DTO\File\Upload\IFile;
use KSP\Core\Service\File\Upload\IFileService;
use Symfony\Component\Mime\MimeTypes;

class FileService implements IFileService {

    private IniConfigService $iniConfigService;

    public function __construct(IniConfigService $iniConfigService) {
        $this->iniConfigService = $iniConfigService;
    }

    public function validateUploadedFile(IFile $file): bool {
        $error          = $file->getError();
        $tmpName        = $file->getTmpName();
        $type           = $file->getType();
        $size           = $file->getSize();
        $isUploadedFile = is_uploaded_file($tmpName);
        $maxSize        = $this->iniConfigService->getValue("upload_max_filesize", -1);

        return
            0 === $error
            && true === is_string($tmpName)
            && true === is_string($type)
            && true === $isUploadedFile
            && $size > $maxSize;
    }

    public function toCoreFile(IFile $file): ICoreFile {
        $mimeTypes  = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($file->getType());
        $extensions = array_values($extensions);

        $coreFile = new File();
        $coreFile->setExtension($extensions[0]);
        $coreFile->setHash(md5_file($file->getTmpName()));
        $coreFile->setTemporaryPath($file->getTmpName());
        $coreFile->setMimeType($file->getType());
        $coreFile->setName($file->getName());
        $coreFile->setSize($file->getSize());
        $coreFile->setCreateTs(new DateTime());
        return $coreFile;
    }

    public function moveUploadedFile(ICoreFile $file): bool {
        return move_uploaded_file($file->getTemporaryPath(), $file->getFullPath());
    }

}

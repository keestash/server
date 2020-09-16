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
use Keestash\Core\DTO\File\File;
use Keestash\Core\Service\Config\IniConfigService;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Service\File\Upload\IFileService;
use Symfony\Component\Mime\MimeTypes;

class FileService implements IFileService {

    private IniConfigService $iniConfigService;

    public function __construct(IniConfigService $iniConfigService) {
        $this->iniConfigService = $iniConfigService;
    }

    public function validateUploadedFile(array $file): bool {
        $error          = $file['error'] ?? -1;
        $tmpName        = $file["tmp_name"] ?? null;
        $type           = $file["type"] ?? null;
        $size           = $file["size"] ?? -10;
        $isUploadedFile = is_uploaded_file($tmpName);
        $maxSize        = $this->iniConfigService->getValue("upload_max_filesize", -1);

        return
            0 === $error
            && true === is_string($tmpName)
            && true === is_string($type)
            && true === $isUploadedFile
            && $size > $maxSize;
    }

    public function toFile(array $fileArray): IFile {
        $mimeTypes  = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($fileArray["type"]);
        $extensions = array_values($extensions);

        $file = new File();
        $file->setExtension($extensions[0]);
        $file->setHash(md5_file($fileArray["tmp_name"]));
        $file->setTemporaryPath($fileArray["tmp_name"]);
        $file->setMimeType($fileArray["type"]);
        $file->setName($fileArray["name"]);
        $file->setSize($fileArray["size"]);
        $file->setCreateTs(new DateTime());
        return $file;
    }

    public function moveUploadedFile(IFile $file): bool {
        return move_uploaded_file($file->getTemporaryPath(), $file->getFullPath());
    }

}

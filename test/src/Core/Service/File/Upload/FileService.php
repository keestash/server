<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KST\Service\Core\Service\File\Upload;

use Keestash\Core\DTO\File\Validation\Result;
use KSP\Core\DTO\File\IFile as ICoreFile;
use KSP\Core\DTO\File\Upload\IFile;
use KSP\Core\DTO\File\Validation\IResult;
use KSP\Core\Service\Config\IIniConfigService;
use KSP\Core\Service\File\Upload\IFileService;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use Psr\Log\LoggerInterface;

class FileService implements IFileService {

    public function __construct(
        private readonly \Keestash\Core\Service\File\Upload\FileService $fileService
        , private readonly IIniConfigService                            $iniConfigService
        , private readonly LoggerInterface                              $logger
    ) {
    }

    public function toFile(UploadedFileInterface $file): IFile {
        return $this->fileService->toFile($file);
    }

    public function validateUploadedFile(IFile $file): IResult {
        $result = new Result();
        /** @var UploadedFile|IFile $file */
        $error             = $file->getError();
        $tmpName           = $file->getTmpName();
        $size              = $file->getSize();
        $uploadMaxFileSize = $this->iniConfigService->getValue("upload_max_filesize", -1);
        $maxSize           = $this->iniConfigService->toBytes((string) $uploadMaxFileSize);

        if (0 !== $error) {
            $result->add(
                sprintf('upload error code: %s', $error)
            );
        }

        if (false === file_exists($tmpName)) {
            $result->add(
                sprintf('file does not exist: %s', $tmpName)
            );
        }

        if (null === $size || $maxSize < $size) {
            $result->add(
                sprintf('file %s has a total size of %s and is larger than allowed (%s)'
                    , $tmpName
                    , null === $size
                        ? 'null'
                        : $size
                    , $maxSize
                )
            );
        }

        if ($result->getResults()->length() > 0) {
            $this->logger->error('error while validating files', ['results' => $result->getResults()->toArray()]);
        }

        return $result;
    }

    public function toCoreFile(IFile $file): ICoreFile {
        return $this->fileService->toCoreFile($file);
    }

    public function moveUploadedFile(ICoreFile $file): bool {
        return true;
    }

    public function removeUploadedFile(ICoreFile $file): bool {
        return $this->fileService->removeUploadedFile($file);
    }

}
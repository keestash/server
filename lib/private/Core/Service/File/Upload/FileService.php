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
use Keestash\Core\DTO\File\Validation\Result;
use Keestash\Core\Service\Config\IniConfigService;
use KSP\Core\DTO\File\IFile as ICoreFile;
use KSP\Core\DTO\File\Upload\IFile;
use KSP\Core\DTO\File\Validation\IResult;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\File\Upload\IFileService;
use Laminas\Diactoros\UploadedFile;
use Psr\Http\Message\UploadedFileInterface;
use Symfony\Component\Mime\MimeTypes;

class FileService implements IFileService {

    private IniConfigService $iniConfigService;
    private ILogger          $logger;

    public function __construct(
        IniConfigService $iniConfigService
        , ILogger        $logger
    ) {
        $this->iniConfigService = $iniConfigService;
        $this->logger           = $logger;
    }

    public function toFile(UploadedFileInterface $file): IFile {
        $uri = (string) $file->getStream()->getMetadata('uri');

        /** @var \Keestash\Core\DTO\File\Upload\File $file */
        $file = \Keestash\Core\DTO\File\Upload\File::fromUploadedFile($file);
        $file->setTmpName($uri);
        $file->setType(
            (string) mime_content_type($uri)
        );
        return $file;
    }

    public function validateUploadedFile(IFile $file): IResult {
        $result = new Result();
        /** @var UploadedFile|IFile $file */
        $error          = $file->getError();
        $tmpName        = $file->getTmpName();
        $size           = $file->getSize();
        $isUploadedFile = is_uploaded_file($tmpName);
        $maxSize        = (int) $this->iniConfigService->getValue("upload_max_filesize", -1);

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

        if (false === $isUploadedFile) {
            $result->add(
                sprintf('file %s is not a uploaded file', $tmpName)
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
        $mimeTypes  = new MimeTypes();
        $extensions = $mimeTypes->getExtensions($file->getType());
        $extensions = array_values($extensions);

        $coreFile = new File();
        $coreFile->setExtension($extensions[0]);
        $coreFile->setHash((string) md5_file($file->getTmpName()));
        $coreFile->setTemporaryPath($file->getTmpName());
        $coreFile->setMimeType($file->getType());
        $coreFile->setName((string) $file->getClientFilename());
        $coreFile->setSize((int) $file->getSize());
        $coreFile->setCreateTs(new DateTime());
        return $coreFile;
    }

    public function moveUploadedFile(ICoreFile $file): bool {
        $temporaryPath = $file->getTemporaryPath();
        if (null === $temporaryPath) return false;
        return move_uploaded_file($temporaryPath, $file->getFullPath());
    }

    public function removeUploadedFile(ICoreFile $file): bool {
        return unlink($file->getFullPath());
    }

}

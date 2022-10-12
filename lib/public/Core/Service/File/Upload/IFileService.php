<?php
declare(strict_types=1);
/**
 * Keestash
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

namespace KSP\Core\Service\File\Upload;

use KSP\Core\DTO\File\IFile as ICoreFile;
use KSP\Core\DTO\File\Upload\IFile;
use KSP\Core\DTO\File\Validation\IResult;
use Psr\Http\Message\UploadedFileInterface;

interface IFileService {

    /**
     * @param UploadedFileInterface $file
     * @return IFile
     */
    public function toFile(UploadedFileInterface $file): IFile;

    /**
     * @param IFile $file
     * @return IResult
     */
    public function validateUploadedFile(IFile $file): IResult;

    /**
     * @param IFile $file
     * @return ICoreFile
     */
    public function toCoreFile(IFile $file): ICoreFile;

    /**
     * @param ICoreFile $file
     * @return bool
     */
    public function moveUploadedFile(ICoreFile $file): bool;

    /**
     * @param ICoreFile $file
     * @return bool
     */
    public function removeUploadedFile(ICoreFile $file): bool;

}

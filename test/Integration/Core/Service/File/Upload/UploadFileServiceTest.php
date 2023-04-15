<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Integration\Core\Service\File\Upload;

use GuzzleHttp\Psr7\UploadedFile;
use KSP\Core\DTO\File\Upload\IFile;
use KSP\Core\Service\File\Upload\IFileService;
use KST\Integration\TestCase;

class UploadFileServiceTest extends TestCase {

    private IFileService $fileService;

    protected function setUp(): void {
        parent::setUp();
        $this->fileService = $this->getService(IFileService::class);
    }

    public function testToFile(): void {
        $file       = __DIR__ . '/uploadedfileservicetestfile.txt';
        $fileSize   = filesize($file);
        $fileObject = $this->fileService->toFile(
            new UploadedFile(
                $file
                , $fileSize
                , 0
            )
        );

        $this->assertInstanceOf(IFile::class, $fileObject);
        $this->assertTrue($fileObject->getSize() === $fileSize);
        $this->assertTrue($fileObject->getType() === 'text/plain');
        $this->assertTrue($fileObject->getError() === 0);
    }

    public function testValidateUploadedFile(): void {
        $file     = __DIR__ . '/uploadedfileservicetestfile.txt';
        $fileSize = filesize($file);
        $result   = $this->fileService->validateUploadedFile(
            $this->fileService->toFile(
                new UploadedFile(
                    $file
                    , $fileSize
                    , 0
                )
            )
        );

        $this->assertTrue(0 === $result->getResults()->length());
    }

    public function testToCoreFile(): void {
        $file     = __DIR__ . '/uploadedfileservicetestfile.txt';
        $fileSize = filesize($file);
        $file     = $this->fileService->toFile(
            new UploadedFile(
                $file
                , $fileSize
                , 0
            )
        );
        $coreFile = $this->fileService->toCoreFile($file);

        $this->assertInstanceOf(\KSP\Core\DTO\File\IFile::class, $coreFile);
        $this->assertTrue($coreFile->getSize() === $file->getSize());
        $this->assertTrue($coreFile->getMimeType() === $file->getType());
    }

    public function testMoveUploadedFileAndRemoveUploadedFile(): void {
        $file     = __DIR__ . '/uploadedfileservicetestfile.txt';
        $fileSize = filesize($file);
        $file     = $this->fileService->toFile(
            new UploadedFile(
                $file
                , $fileSize
                , 0
            )
        );
        $coreFile = $this->fileService->toCoreFile($file);
        $coreFile->setDirectory(__DIR__);
        $coreFile->setName('uploadedfileservicetestfile');

        // both are mocked, so no actual test needed
        $this->assertTrue(
            true === $this->fileService->moveUploadedFile($coreFile)
        );
        $this->assertTrue(
            true === $this->fileService->removeUploadedFile($coreFile)
        );
    }

}
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

namespace KST\Integration\Core\Service\File;

use DateTimeImmutable;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\URI\URL\URL;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\IFileService;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class FileServiceTest extends TestCase {

    private IFileService $fileService;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->fileService = $this->getService(IFileService::class);
    }

    public function testGetProfileImagePath(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $url  = $this->fileService->getProfileImagePath($user);
        $this->assertInstanceOf(URL::class, $url);
        $this->assertStringContainsString('/asset/img/profile-picture.png', $url->getIdentifier());
        $this->removeUser($user);
    }

    public function testGetDefaultImage(): void {
        $defaultImage = $this->fileService->getDefaultImage();
        $this->assertInstanceOf(IFile::class, $defaultImage);
        $this->assertStringContainsString('/asset/img/profile-picture.png', $defaultImage->getFullPath());
    }

    public function testGetProfileImage(): void {
        $user         = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $profileImage = $this->fileService->getProfileImage($user);
        $this->assertInstanceOf(URL::class, $profileImage);
        $this->assertStringContainsString('profile_image_' . $user->getId(), $profileImage->getIdentifier());
        $this->removeUser($user);
    }

    public function testGetProfileImageName(): void {
        $user         = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $profileImage = $this->fileService->getProfileImageName($user);
        $this->assertTrue($profileImage === "profile_image_{$user->getId()}");
        $this->removeUser($user);
    }

    public function testRead(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $file = new File();
        $file->setName('fileServiceTest');
        $file->setCreateTs(new DateTimeImmutable());
        $file->setMimeType('text/plain');
        $file->setTemporaryPath(__DIR__);
        $file->setExtension('txt');
        $file->setDirectory(__DIR__);
        $file->setSize(1);
        $file->setContent("this is a test file");
        $file->setHash(md5((string) time()));
        $file->setOwner($user);

        $file = $fileRepository->add($file);

        $this->assertInstanceOf(IFile::class, $file);

        $put = file_put_contents($file->getFullPath(), $file->getContent());

        $this->assertTrue(false !== $put);

        $url = new URL();
        $url->setIdentifier($file->getFullPath());

        $retrieved = $this->fileService->read($url);
        $this->assertTrue($retrieved->getId() === $file->getId());
        $this->assertTrue($retrieved->getName() === $file->getName());
        $this->assertTrue($retrieved->getMimeType() === $file->getMimeType());
        $this->assertTrue($retrieved->getExtension() === $file->getExtension());
        $this->assertTrue($retrieved->getDirectory() === $file->getDirectory());
        $this->assertTrue($retrieved->getSize() === $file->getSize());
        $this->assertTrue($retrieved->getHash() === $file->getHash());
        $this->assertTrue($retrieved->getOwner()->getId() === $file->getOwner()->getId());
        $unlinked = unlink($file->getFullPath());
        $this->assertTrue(true === $unlinked);
        $this->removeUser($user);
    }

}
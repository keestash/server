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

namespace KST\Integration\Core\Repository\File;

use DateTimeImmutable;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\File\FileList;
use Keestash\Core\DTO\URI\URI;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Repository\File\IFileRepository;
use KST\Integration\TestCase;
use Ramsey\Uuid\Uuid;

class FileRepositoryTest extends TestCase {

    public function testAdd(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));
        $this->removeUser($user);
    }

    public function testUpdate(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $newName = $file->getName() . 'addedFile';
        $addedFile->setName($newName);
        $updatedFile = $fileRepository->update($addedFile);
        $this->assertTrue($updatedFile instanceof IFile);
        $this->assertTrue($updatedFile->getName() === $newName);
        $this->removeUser($user);
    }

    public function testAddAll(): void {
        $fileList = new FileList();
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');

        $fileList->add($file);

        $file = new File();
        $file->setName(FileRepositoryTest::class . '1');
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file.1');
        $file->setSize(2);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz1.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy1.txt');

        $fileList->add($file);
        $fileList = $fileRepository->addAll($fileList);
        $this->assertTrue($fileList instanceof FileList);
        $this->assertTrue($fileList->length() === 2);
        $this->removeUser($user);
    }

    public function testRemove(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $fileRepository->remove($file);
        $this->removeUser($user);
    }

    public function testRemoveForUser(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $fileRepository->removeForUser($user);
        $this->removeUser($user);
    }

    public function testRemoveAll(): void {
        $fileList = new FileList();
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');

        $fileList->add($file);

        $file = new File();
        $file->setName(FileRepositoryTest::class . '1');
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file.1');
        $file->setSize(2);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz1.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy1.txt');

        $fileList->add($file);
        $fileList = $fileRepository->addAll($fileList);
        $this->assertTrue($fileList instanceof FileList);
        $this->assertTrue($fileList->length() === 2);

        $fileRepository->removeAll($fileList);
        $this->removeUser($user);
    }

    public function testGet(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $retrievedFile = $fileRepository->get($addedFile->getId());
        $this->assertTrue($retrievedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($retrievedFile->getId()));
        $this->assertTrue($retrievedFile->getId() === $addedFile->getId());
        $this->removeUser($user);
    }

    public function testGetByUri(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $uri = new Uri();
        $uri->setIdentifier($addedFile->getFullPath());
        $retrievedFile = $fileRepository->getByUri($uri);
        $this->assertTrue($retrievedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($retrievedFile->getId()));
        $this->assertTrue($retrievedFile->getId() === $addedFile->getId());
        $this->removeUser($user);
    }

    public function testGetByName(): void {
        /** @var IFileRepository $fileRepository */
        $fileRepository = $this->getService(IFileRepository::class);

        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $file = new File();
        $file->setName(FileRepositoryTest::class);
        $file->setCreateTs(new DateTimeImmutable());
        $file->setOwner($user);
        $file->setHash(md5((string) time()));
        $file->setContent('test.file');
        $file->setSize(1);
        $file->setMimeType('txt');
        $file->setTemporaryPath('/tmp/xyz.txt');
        $file->setExtension('txt');
        $file->setDirectory('/tmp/xzy.txt');
        $addedFile = $fileRepository->add($file);
        $this->assertTrue($addedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($addedFile->getId()));

        $retrievedFile = $fileRepository->getByName($addedFile->getName());
        $this->assertTrue($retrievedFile instanceof IFile);
        $this->assertTrue(true === is_numeric($retrievedFile->getId()));
        $this->assertTrue($retrievedFile->getId() === $addedFile->getId());
        $this->removeUser($user);
    }

}
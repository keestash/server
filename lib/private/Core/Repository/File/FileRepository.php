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

namespace Keestash\Core\Repository\File;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\File\FileList;
use Keestash\Exception\KeestashException;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;

class FileRepository implements IFileRepository {

    private IUserRepository  $userRepository;
    private IDateTimeService $dateTimeService;
    private IBackend         $backend;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
        , IDateTimeService $dateTimeService
    ) {
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
    }

    public function addAll(FileList &$files): bool {
        $addedAll = false;
        /** @var IFile $file */
        foreach ($files as $file) {
            $fileId   = $this->add($file);
            $addedAll = false;

            if (null !== $fileId) {
                $file->setId($fileId);
                $addedAll = true;
            }

        }
        return $addedAll;
    }

    public function add(IFile $file): ?int {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert("`file`")
            ->values(
                [
                    "`name`"        => '?'
                    , "`path`"      => '?'
                    , "`mime_type`" => '?'
                    , "`hash`"      => '?'
                    , "`extension`" => '?'
                    , "`size`"      => '?'
                    , "`user_id`"   => '?'
                    , "`create_ts`" => '?'
                    , "`directory`" => '?'
                ]
            )
            ->setParameter(0, $file->getName())
            ->setParameter(1, $file->getFullPath())
            ->setParameter(2, $file->getMimeType())
            ->setParameter(3, $file->getHash())
            ->setParameter(4, $file->getExtension())
            ->setParameter(5, $file->getSize())
            ->setParameter(6, $file->getOwner()->getId())
            ->setParameter(7, $this->dateTimeService->toYMDHIS($file->getCreateTs()))
            ->setParameter(8, $file->getDirectory())
            ->execute();

        $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;
    }

    public function removeAll(FileList $files): bool {
        $removedAll = false;
        foreach ($files as $file) {
            $removed    = $this->remove($file);
            $removedAll = $removedAll || $removed;
        }
        return $removedAll;
    }

    public function remove(IFile $file): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('file')
                ->where('id = ?')
                ->setParameter(0, $file->getId())
                ->execute() > 0;
    }

    public function getAll(ArrayList $fileIds): FileList {

        $fileList = new FileList();

        foreach ($fileIds as $id) {
            /** @var IFile $file */
            $file = $this->get($id);
            $fileList->add($file);
        }

        return $fileList;
    }

    public function get(int $id): ?IFile {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'directory'
                , 'path'
                , 'mime_type'
                , 'hash'
                , 'extension'
                , 'size'
                , 'create_ts'
                , 'user_id'
            ]
        )
            ->from('file')
            ->where('id = ?')
            ->setParameter(0, $id);
        $files     = $queryBuilder->execute()->fetchAllAssociative();
        $fileCount = count($files);

        if (0 === $fileCount) {
            return null;
        }

        if ($fileCount > 1) {
            throw new KeestashException("found more then one user for the given name");
        }


        $row       = $files[0];
        $id        = $row["id"];
        $name      = $row["name"];
        $directory = $row["directory"];
        $mimeType  = $row["mime_type"];
        $hash      = $row["hash"];
        $extension = $row["extension"];
        $size      = $row["size"];
        $createTs  = $row["create_ts"];
        $userId    = $row["user_id"];

        $user = $this->userRepository->getUserById((string) $userId);

        if (null == $user) {
            throw new KeestashException();
        }

        $file = new File();
        $file->setId((int) $id);
        $file->setName($name);
        $file->setDirectory($directory);
        $file->setMimeType($mimeType);
        $file->setHash($hash);
        $file->setExtension($extension);
        $file->setSize((int) $size);
        $file->setOwner($user);
        $file->setCreateTs(
            $this->dateTimeService->fromFormat($createTs)
        );

        return $file;
    }

    public function getByUri(IUniformResourceIdentifier $uri): ?IFile {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();

        $queryBuilder = $queryBuilder->select(
            [
                'id'
                , 'name'
                , 'path'
                , 'mime_type'
                , 'hash'
                , 'extension'
                , 'size'
                , 'user_id'
                , 'create_ts'
                , 'directory'
            ]
        )
            ->from('file')
            ->where('path = ?')
            ->orWhere('path like ?')
            ->setParameter(0, $uri->getIdentifier())
            ->setParameter(1, "{$uri->getIdentifier()}%");
        $files        = $queryBuilder->execute()->fetchAllNumeric();

        $file = null;
        foreach ($files as $row) {
            $id        = $row[0];
            $name      = $row[1];
            $path      = $row[2];
            $mimeType  = $row[3];
            $hash      = $row[4];
            $extension = $row[5];
            $size      = $row[6];
            $userId    = $row[7];
            $createTs  = $row[8];
            $directory = $row[9];

            $user = $this->userRepository->getUserById((string) $userId);

            if (null == $user) {
                throw new KeestashException();
            }

            $file = new File();
            $file->setId((int) $id);
            $file->setName($name);
            $file->setDirectory($directory);
            $file->setMimeType($mimeType);
            $file->setHash($hash);
            $file->setExtension($extension);
            $file->setSize((int) $size);
            $file->setOwner($user);
            $file->setCreateTs(
                $this->dateTimeService->fromFormat($createTs)
            );

        }

        return $file;
    }

    public function removeForUser(IUser $user): bool {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        return $queryBuilder->delete('file')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->execute() > 0;
    }

    /**
     * @param IFile $file
     *
     * @return IFile
     * @throws PasswordManagerException
     */
    public function update(IFile $file): IFile {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->update('file')
            ->set('name', '?')
            ->set('directory', '?')
            ->set('path', '?')
            ->set('mime_type', '?')
            ->set('hash', '?')
            ->set('extension', '?')
            ->set('size', '?')
            ->set('user_id', '?')
            ->where('id = ?')
            ->setParameter(0, $file->getName())
            ->setParameter(1, $file->getDirectory())
            ->setParameter(2, $file->getFullPath())
            ->setParameter(3, $file->getMimeType())
            ->setParameter(4, $file->getHash())
            ->setParameter(5, $file->getExtension())
            ->setParameter(6, $file->getSize())
            ->setParameter(7, $file->getOwner()->getId())
            ->setParameter(8, $file->getId());
        $rowCount = $queryBuilder->execute();

        if (0 === $rowCount) {
            throw new PasswordManagerException('no rows updated');
        }
        return $file;
    }

}

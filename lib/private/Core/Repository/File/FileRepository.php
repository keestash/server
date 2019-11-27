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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use doganoo\PHPUtil\Log\FileLogger;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Exception;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\File\FileList;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use PDO;

class FileRepository extends AbstractRepository implements IFileRepository {

    private $userRepository = null;

    public function __construct(
        IBackend $backend
        , IUserRepository $userRepository
    ) {
        parent::__construct($backend);

        $this->userRepository = $userRepository;
    }

    public function add(IFile $file): ?int {
        $sql = "insert into `file` (
                   `name`
                  , `path`
                  , `mime_type`
                  , `hash`
                  , `extension`
                  , `size`
                  , `user_id`
                  , `create_ts`
                  )
                  values (
                          :name
                          , :path
                          , :mime_type
                          , :hash
                          , :extension
                          , :size
                          , :user_id
                          , :create_ts
                          );";

        $statement = parent::prepareStatement($sql);

        $name      = $file->getName();
        $path      = $file->getFullPath();
        $mimeType  = $file->getMimeType();
        $hash      = $file->getHash();
        $extension = $file->getExtension();
        $size      = $file->getSize();
        $userId    = $file->getOwner()->getId();
        $createTs  = $file->getCreateTs();
        $createTs  = DateTimeUtil::formatMysqlDateTime($createTs);

        $statement->bindParam("name", $name);
        $statement->bindParam("path", $path);
        $statement->bindParam("mime_type", $mimeType);
        $statement->bindParam("hash", $hash);
        $statement->bindParam("extension", $extension);
        $statement->bindParam("size", $size);
        $statement->bindParam("user_id", $userId);
        $statement->bindParam("create_ts", $createTs);

        if (false === $statement->execute()) return null;

        $lastInsertId = (int) parent::getLastInsertId();

        if (0 === $lastInsertId) return null;
        return $lastInsertId;
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

    public function remove(IFile $file): bool {
        $sql       = "delete from `file` where `id` = :file_id;";
        $statement = parent::prepareStatement($sql);

        if (null === $statement) return false;

        $fileId = $file->getId();
        $statement->bindParam("file_id", $fileId);
        return $statement->execute();
    }

    public function removeAll(FileList $files): bool {
        $removedAll = false;
        foreach ($files as $file) {
            $removed    = $this->remove($file);
            $removedAll = $removedAll || $removed;
        }
        return $removedAll;
    }

    public function get(int $id): ?IFile {

        $sql = "select 
                        `id`
                        , `name`
                        , `path`
                        , `mime_type`
                        , `hash`
                        , `extension`
                        , `size`
                        , `user_id`
                        , `create_ts`
                 from `file`
                    where `id` = :id
                 ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return null;
        }

        $statement->bindParam("id", $id);
        $statement->execute();

        $file = null;
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id        = $row[0];
            $name      = $row[1];
            $path      = $row[2];
            $mimeType  = $row[3];
            $hash      = $row[4];
            $extension = $row[5];
            $size      = $row[6];
            $userId    = $row[7];
            $createTs  = $row[8];

            $file = new File();
            $file->setId((int) $id);
            $file->setName($name);
            $file->setDirectory($path);
            $file->setMimeType($mimeType);
            $file->setHash($hash);
            $file->setExtension($extension);
            $file->setSize((int) $size);
            $file->setOwner(
                $this->userRepository->getUserById((string) $userId)
            );
            $file->setCreateTs(
                DateTimeUtil::fromMysqlDateTime($createTs)
            );

        }
        return $file;
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

    public function getByUri(IUniformResourceIdentifier $uri): ?IFile {
        try {
            $name = basename($uri->getIdentifier());
            $path = substr($uri->getIdentifier(), 0, strpos($uri->getIdentifier(), $name));

            $sql = "select 
                        `id`
                        , `name`
                        , `path`
                        , `mime_type`
                        , `hash`
                        , `extension`
                        , `size`
                        , `user_id`
                        , `create_ts`
                 from `file`
                    where `path` = :path
                    and `name` = :name
                 ";

            $statement = parent::prepareStatement($sql);

            if (null === $statement) {
                return null;
            }

            $statement->bindParam("path", $path);
            $statement->bindParam("name", $name);

            $statement->execute();

            $file = null;
            while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
                $id        = $row[0];
                $name      = $row[1];
                $path      = $row[2];
                $mimeType  = $row[3];
                $hash      = $row[4];
                $extension = $row[5];
                $size      = $row[6];
                $userId    = $row[7];
                $createTs  = $row[8];

                $file = new File();
                $file->setId((int) $id);
                $file->setName($name);
                $file->setDirectory($path);
                $file->setMimeType($mimeType);
                $file->setHash($hash);
                $file->setExtension($extension);
                $file->setSize((int) $size);
                $file->setOwner(
                    $this->userRepository->getUserById((string) $userId)
                );
                $file->setCreateTs(
                    DateTimeUtil::fromMysqlDateTime($createTs)
                );

            }

            return $file;
        } catch (Exception $e) {
            FileLogger::debug($e->getTraceAsString());
        }
        return null;
    }

}
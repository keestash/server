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

use Doctrine\DBAL\Exception;
use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\DTO\File\File;
use Keestash\Core\DTO\File\FileList;
use Keestash\Exception\File\FileNotCreatedException;
use Keestash\Exception\File\FileNotDeletedException;
use Keestash\Exception\File\FileNotFoundException;
use Keestash\Exception\File\FileNotUpdatedException;
use Keestash\Exception\KeestashException;
use Keestash\Exception\TooManyFilesException;
use Keestash\Exception\TooManyRowsException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Logger\ILogger;

class FileRepository implements IFileRepository {

    private IUserRepository  $userRepository;
    private IDateTimeService $dateTimeService;
    private IBackend         $backend;
    private ILogger          $logger;

    public function __construct(
        IBackend           $backend
        , IUserRepository  $userRepository
        , IDateTimeService $dateTimeService
        , ILogger          $logger
    ) {
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
        $this->logger          = $logger;
    }

    /**
     * @param FileList $files
     * @return FileList
     * @throws FileNotCreatedException
     */
    public function addAll(FileList $files): FileList {
        /** @var IFile $file */
        foreach ($files as $key => $file) {
            $files->set($key,
                $this->add($file)
            );
        }
        return $files;
    }

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotCreatedException
     */
    public function add(IFile $file): IFile {
        try {
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
                ->executeStatement();

            $lastInsertId = (int) $this->backend->getConnection()->lastInsertId();

            if (0 === $lastInsertId) {
                throw new FileNotCreatedException();
            }

            $file->setId($lastInsertId);
            return $file;
        } catch (Exception $exception) {
            $this->logger->error('error creating file', ['exception' => $exception]);
            throw new FileNotCreatedException();
        }
    }

    /**
     * @param FileList $files
     * @return FileList
     * @throws FileNotDeletedException
     */
    public function removeAll(FileList $files): FileList {
        foreach ($files as $file) {
            $this->remove($file);
        }
        return $files;
    }

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotDeletedException
     */
    public function remove(IFile $file): IFile {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('file')
                ->where('id = ?')
                ->setParameter(0, $file->getId())
                ->executeStatement();
            return $file;
        } catch (Exception $exception) {
            $this->logger->error('file not deleted', ['exception' => $exception]);
            throw new FileNotDeletedException();
        }
    }

    /**
     * @param int $id
     * @return IFile
     * @throws FileNotFoundException
     * @throws KeestashException
     */
    public function get(int $id): IFile {
        try {
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
            $files     = $queryBuilder->executeQuery()->fetchAllAssociative();
            $fileCount = count($files);

            if (0 === $fileCount) {
                throw new FileNotFoundException();
            }

            if ($fileCount > 1) {
                throw new TooManyRowsException("found more then one user for the given name");
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
                throw new UserNotFoundException();
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
        } catch (Exception $exception) {
            $this->logger->error('error with retrieving file', ['exception' => $exception]);
            throw new FileNotFoundException();
        }
    }

    /**
     * @param string $name
     * @return IFile
     * @throws FileNotFoundException
     * @throws UserNotFoundException
     */
    public function getByName(string $name): IFile {
        try {
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
                ->where('name = ?')
                ->orWhere('name like ?')
                ->setParameter(0, $name)
                ->setParameter(1, "$name%");
            $files        = $queryBuilder->executeQuery()->fetchAllNumeric();
            $fileCount    = count($files);

            if (0 === $fileCount) {
                throw new FileNotFoundException();
            }

            $file = new File();
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
        } catch (Exception $exception) {
            $this->logger->error('error while getting file', ['exception' => $exception]);
            throw new FileNotFoundException();
        }
    }

    /**
     * @param IUniformResourceIdentifier $uri
     * @return IFile
     * @throws FileNotFoundException
     */
    public function getByUri(IUniformResourceIdentifier $uri): IFile {
        try {
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
            $files        = $queryBuilder->executeQuery()->fetchAllNumeric();
            $fileCount    = count($files);

            if (0 === $fileCount) {
                throw new FileNotFoundException();
            }
            $file = new File();
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
        } catch (Exception|UserNotFoundException $exception) {
            $this->logger->error('error retrieving file', ['exception' => $exception]);
            throw new FileNotFoundException();
        }
    }

    /**
     * @param IUser $user
     * @return void
     * @throws FileNotDeletedException
     */
    public function removeForUser(IUser $user): void {
        try {
            $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
            $queryBuilder->delete('file')
                ->where('user_id = ?')
                ->setParameter(0, $user->getId())
                ->executeStatement();
        } catch (Exception $exception) {
            $this->logger->error('error removing for user', ['exception' => $exception]);
            throw new FileNotDeletedException();
        }
    }

    /**
     * @param IFile $file
     * @return IFile
     * @throws FileNotUpdatedException
     */
    public function update(IFile $file): IFile {
        try {
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
            $rowCount = $queryBuilder->executeStatement();

            if (0 === $rowCount) {
                throw new FileNotUpdatedException('no rows updated');
            }
            return $file;
        } catch (Exception $exception) {
            $this->logger->error('error updating file', ['exception' => $exception]);
            throw new FileNotUpdatedException();
        }
    }

}

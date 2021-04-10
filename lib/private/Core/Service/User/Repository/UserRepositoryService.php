<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\Service\User\Repository;

use Exception;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use Keestash\Exception\UserNotCreatedException;
use Keestash\Exception\UserNotDeletedException;
use Keestash\Exception\UserNotLockedException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\Repository\IUserRepositoryService;

class UserRepositoryService implements IUserRepositoryService {

    private IApiLogRepository    $apiLogRepository;
    private IFileRepository      $fileRepository;
    private IUserKeyRepository   $keyRepository;
    private IUserRepository      $userRepository;
    private IUserStateRepository $userStateRepository;
    private FileService          $fileService;
    private ILogger              $logger;
    private IEventManager        $eventManager;

    public function __construct(
        IApiLogRepository $apiLogRepository
        , IFileRepository $fileRepository
        , IUserKeyRepository $keyRepository
        , IUserRepository $userRepository
        , IUserStateRepository $userStateRepository
        , FileService $fileService
        , ILogger $logger
        , IEventManager $eventManager
    ) {
        $this->apiLogRepository    = $apiLogRepository;
        $this->fileRepository      = $fileRepository;
        $this->keyRepository       = $keyRepository;
        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
        $this->fileService         = $fileService;
        $this->logger              = $logger;
        $this->eventManager        = $eventManager;
    }

    public function removeUser(IUser $user): array {

        $logsRemoved  = $this->apiLogRepository->removeForUser($user);
        $filesRemoved = $this->fileRepository->removeForUser($user);
        $keysRemoved  = $this->keyRepository->remove($user);
        $userRemoved  = $this->userRepository->remove($user);

        return [
            "logs_removed"    => $logsRemoved
            , "files_removed" => $filesRemoved
            , "keys_removed"  => $keysRemoved
            , "user_removed"  => $user
            , "success"       =>
                true === $logsRemoved
                && true === $filesRemoved
                && true === $keysRemoved
                && true === $userRemoved
        ];
    }

    public function createSystemUser(IUser $user): bool {
        $user->setLocked(true);
        $file = $this->fileService->getDefaultImage();
        $file->setOwner($user);
        try {
            $this->createUser($user, $file);
            return true;
        } catch (Exception $exception) {
            $this->logger->error(json_encode([$exception->getMessage(), $exception->getTraceAsString()]));
            return false;
        }
    }

    public function createUser(IUser $user, ?IFile $file = null): IUser {

        $userId = $this->userRepository->insert($user);

        if (null === $userId) {
            throw new UserNotCreatedException();
        }

        $user->setId($userId);

        $this->eventManager->execute(new UserCreatedEvent($user));

        if (false === $user->isLocked()) return $user;

        $locked = $this->userStateRepository->lock($user);

        if (false === $locked) {
            throw new UserNotLockedException();
        }

        if (false === $user->isDeleted()) return $user;

        $deleted = $this->userStateRepository->delete($user);

        if (false === $deleted) {
            throw new UserNotDeletedException();
        }

        if (null === $file) return $user;

        $this->fileRepository->add($file);

        return $user;
    }

    public function userExistsByName(string $name): bool {
        $users = $this->userRepository->getAll();

        /** @var IUser $user */
        foreach ($users as $user) {
            if ($user->getName() === $name) return true;
        }

        return false;
    }

    public function updateUser(IUser $updatedUser, IUser $oldUser): bool {
        $updated = $this->userRepository->update($updatedUser);

        $this->eventManager->execute(
            new UserUpdatedEvent(
                $updatedUser
                , $oldUser
                , $updated
            )
        );

        return $updated;
    }

}
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
use Keestash\Exception\File\FileNotCreatedException;
use Keestash\Exception\File\FileNotDeletedException;
use Keestash\Exception\User\State\UserStateNotRemovedException;
use Keestash\Exception\User\UserException;
use Keestash\Exception\User\UserNotCreatedException;
use Keestash\Exception\User\UserNotDeletedException;
use Keestash\Exception\User\UserNotFoundException;
use Keestash\Exception\User\UserNotUpdatedException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Log\LoggerInterface;

final readonly class UserRepositoryService implements IUserRepositoryService {

    public function __construct(
        private IApiLogRepository    $apiLogRepository
        , private IFileRepository    $fileRepository
        , private IUserKeyRepository $keyRepository
        , private IUserRepository    $userRepository
        , private FileService        $fileService
        , private LoggerInterface    $logger
        , private IEventService      $eventManager
        , private IUserStateService  $userStateService
    ) {
    }

    /**
     * @param IUser      $user
     * @param IFile|null $file
     * @return IUser
     * @throws UserNotCreatedException
     * @throws FileNotCreatedException
     */
    public function createUser(IUser $user, ?IFile $file = null): IUser {
        $user = $this->userRepository->insert($user);
        $this->eventManager->execute(new UserCreatedEvent($user));

        if (true === $user->isLocked()) {
            $this->userStateService->forceLock($user);
        }

        if (true === $user->isDeleted()) {
            $this->userStateService->forceDelete($user);
        }

        if (null === $file) return $user;

        $this->fileRepository->add($file);

        return $user;
    }

    /**
     * @param IUser $user
     * @return array
     * @throws UserException
     */
    public function removeUser(IUser $user): array {
        try {
            $this->fileRepository->removeForUser($user);
            $this->fileService->removeProfileImage($user);
            $this->keyRepository->remove($user);
            $this->userStateService->clear($user);
            $this->userRepository->remove($user);
        } catch (UserNotDeletedException|UserStateNotRemovedException|FileNotDeletedException $exception) {
            $this->logger->error('error while deleting', ['exception' => $exception]);
            throw new UserException();
        }

        return [
            "logs_removed"    => true
            , "files_removed" => true
            , "keys_removed"  => true
            , "user_removed"  => $user
            , "success"       => true
        ];
    }

    /**
     * @param IUser $user
     * @return bool
     */
    public function createSystemUser(IUser $user): bool {
        $user->setLocked(true);
        $file = $this->fileService->getDefaultImage();
        $file->setOwner($user);
        try {
            $this->createUser($user, $file);
            return true;
        } catch (Exception $exception) {
            $this->logger->error((string) json_encode([$exception->getMessage(), $exception->getTraceAsString()]));
            return false;
        }
    }

    /**
     * @param string $name
     * @return bool
     */
    public function userExistsByName(string $name): bool {
        try {
            $this->userRepository->getUser($name);
            return true;
        } catch (UserNotFoundException $exception) {
            return false;
        }
    }

    /**
     * @param string $id
     * @return bool
     */
    public function userExistsById(string $id): bool {
        try {
            $this->userRepository->getUserById($id);
            return true;
        } catch (UserNotFoundException $exception) {
            return false;
        }
    }

    /**
     * @param string $email
     * @return bool
     */
    public function userExistsByEmail(string $email): bool {
        try {
            $this->userRepository->getUserByEmail($email);
            return true;
        } catch (UserNotFoundException $exception) {
            return false;
        }
    }

    /**
     * @param IUser $updatedUser
     * @param IUser $user
     * @return IUser
     * @throws UserNotUpdatedException
     */
    public function updateUser(IUser $updatedUser, IUser $user): IUser {
        $this->userRepository->update($updatedUser);

        $this->eventManager->execute(
            new UserUpdatedEvent(
                $updatedUser
                , $user
            )
        );
        return $updatedUser;
    }

}

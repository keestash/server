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

namespace Keestash\Core\Service\User;

use DateTime;
use doganoo\DI\DateTime\IDateTimeService;
use Exception;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\Event\UserUpdatedEvent;
use Keestash\Exception\KeyNotCreatedException;
use Keestash\Exception\UserNotCreatedException;
use Keestash\Exception\UserNotDeletedException;
use Keestash\Exception\UserNotLockedException;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;

class UserService {

    /** @var int */
    public const MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD = 8;

    private IApiLogRepository    $apiLogRepository;
    private IFileRepository      $fileRepository;
    private IUserKeyRepository   $keyRepository;
    private IUserRepository      $userRepository;
    private KeyService           $keyService;
    private Legacy               $legacy;
    private IUserStateRepository $userStateRepository;
    private FileService          $fileService;
    private InstanceRepository   $instanceRepository;
    private CredentialService    $credentialService;
    private IDateTimeService     $dateTimeService;
    private ILogger              $logger;

    public function __construct(
        IApiLogRepository $apiLogRepository
        , IFileRepository $fileRepository
        , IUserKeyRepository $keyRepository
        , IUserRepository $userRepository
        , KeyService $keyService
        , Legacy $legacy
        , IUserStateRepository $userStateRepository
        , FileService $fileService
        , InstanceRepository $instanceRepository
        , CredentialService $credentialService
        , IDateTimeService $dateTimeService
        , ILogger $logger
    ) {
        $this->apiLogRepository    = $apiLogRepository;
        $this->fileRepository      = $fileRepository;
        $this->keyRepository       = $keyRepository;
        $this->userRepository      = $userRepository;
        $this->keyService          = $keyService;
        $this->legacy              = $legacy;
        $this->userStateRepository = $userStateRepository;
        $this->fileService         = $fileService;
        $this->instanceRepository  = $instanceRepository;
        $this->credentialService   = $credentialService;
        $this->dateTimeService     = $dateTimeService;
        $this->logger              = $logger;
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

    public function validatePassword(string $password, string $hash): bool {
        return true === password_verify($password, $hash);
    }

    public function passwordHasMinimumRequirements(string $password): bool {
        $passwordLength = strlen($password);

        if (true === $passwordLength < UserService::MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD) return false;

        // minimum 1 number
        if (strlen(preg_replace('/([^0-9]*)/', '', $password)) < 1) return false;

        if (strlen(preg_replace('/([^a-zA-Z]*)/', '', $password)) < 1) return false;

        // Check the number of lower case letters in the password
        if (strlen(preg_replace('/([^a-z]*)/', '', $password)) < 1) return false;

        // Check the number of upper case letters in the password
        if (strlen(preg_replace('/([^A-Z]*)/', '', $password)) < 1) return false;

        // Check the minimum number of symbols in the password.
        if (strlen(preg_replace('/([a-zA-Z0-9]*)/', '', $password)) < 1) return false;

        return true;
    }

    public function validEmail(string $email): bool {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    public function validWebsite(string $email): bool {
        return false !== filter_var($email, FILTER_VALIDATE_URL);
    }

    /**
     * @return IUser
     */
    public function getSystemUser(): IUser {
        $user = new User();
        $user->setName((string) $this->legacy->getApplication()->get("name"));
        $user->setId(IUser::SYSTEM_USER_ID);
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setCreateTs(new DateTime());
        $user->setEmail((string) $this->legacy->getApplication()->get("email"));
        $user->setFirstName((string) $this->legacy->getApplication()->get("name"));
        $user->setLastName((string) $this->legacy->getApplication()->get("name"));
        $user->setPhone((string) $this->legacy->getApplication()->get("phone"));
        $user->setWebsite((string) $this->legacy->getApplication()->get("web"));
        $user->setPassword(
            $this->hashPassword($user->getName())
        );
        $user->setLocked(true);
        return $user;
    }

    /**
     * @return IUser
     */
    public function getDemoUser(): IUser {
        $user = new User();
        $user->setName(IUser::DEMO_USER_NAME);
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setCreateTs(new DateTime());
        $user->setEmail((string) $this->legacy->getApplication()->get("email"));
        $user->setFirstName((string) $this->legacy->getApplication()->get("name"));
        $user->setLastName((string) $this->legacy->getApplication()->get("name"));
        $user->setPhone((string) $this->legacy->getApplication()->get("phone"));
        $user->setWebsite((string) $this->legacy->getApplication()->get("web"));
        $user->setPassword(
            $this->hashPassword(IUser::DEMO_USER_NAME)
        );
        return $user;
    }

    public function getRandomHash(): string {
        return hash("sha256", uniqid("", true));
    }

    public function hashPassword(string $plain): string {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    public function toUser(array $userArray): IUser {
        $user = new User();
        $user->setId((int) $userArray['id']);
        $user->setName($userArray['name']);
        $user->setCreateTs(
            $this->dateTimeService->fromString($userArray['create_ts']['date'])
        );
        $user->setDeleted($userArray['deleted']);
        $user->setEmail($userArray['email']);
        $user->setFirstName($userArray['first_name']);
        $user->setLastName($userArray['last_name']);
        $user->setHash($userArray['hash']);
        $user->setLocked($userArray['locked']);
        $user->setPassword(IUser::VERY_DUMB_ATTEMPT_TO_MOCK_PASSWORDS_ON_SYSTEM_LEVEL_BUT_SECURITY_GOES_FIRST);
        $user->setPhone($userArray['phone']);
        $user->setWebsite($userArray['website']);
        return $user;
    }

    public function toNewUser(array $userArray): IUser {
        $user = new User();
        $user->setCreateTs(new DateTime());
        $user->setName($userArray["user_name"]);
        $user->setEmail($userArray["email"]);
        $user->setLastName($userArray["last_name"]);
        $user->setFirstName($userArray["first_name"]);
        $user->setPassword(
            $this->hashPassword($userArray["password"])
        );
        $user->setPhone($userArray["phone"]);
        $user->setWebsite($userArray["website"]);
        $user->setHash(
            $this->getRandomHash()
        );
        return $user;
    }

    /**
     * @param IUser|User $user
     *
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
            $this->logger->error(json_encode([$exception->getMessage(), $exception->getTraceAsString()]));
            return false;
        }
    }

    /**
     * @param IUser      $user
     * @param IFile|null $file
     *
     * @return IUser
     * @throws KeyNotCreatedException
     * @throws UserNotCreatedException
     * @throws UserNotLockedException
     * @throws UserNotDeletedException
     */
    public function createUser(IUser $user, ?IFile $file = null): IUser {

        $userId = $this->userRepository->insert($user);

        if (null === $userId) {
            throw new UserNotCreatedException();
        }

        $user->setId($userId);

        Keestash::getServer()
            ->getEventManager()
            ->execute(new UserCreatedEvent($user));

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

    public function isDisabled(?IUser $user): bool {
        return null === $user || true === $user->isLocked();
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function userExistsByName(string $name): bool {
        $users = Keestash::getServer()->getUsersFromCache();

        /** @var IUser $user */
        foreach ($users as $user) {
            if ($user->getName() === $name) return true;
        }

        return false;
    }

    public function updateUser(IUser $updatedUser, IUser $oldUser): bool {
        $updated = $this->userRepository->update($updatedUser);

        Keestash::getServer()
            ->getEventManager()
            ->execute(
                new UserUpdatedEvent(
                    $updatedUser
                    , $oldUser
                    , $updated
                )
            );

        return $updated;
    }

}

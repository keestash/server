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
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\File\FileService;
use Keestash\Exception\KeyNotCreatedException;
use Keestash\Exception\UserNotCreatedException;
use Keestash\Exception\UserNotLockedException;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;

class UserService {

    /** @var int */
    public const MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD = 8;

    /** @var IApiLogRepository */
    private $apiLogRepository;
    /** @var IFileRepository */
    private $fileRepository;
    /** @var IEncryptionKeyRepository */
    private $keyRepository;
    /** @var IRoleRepository */
    private $rolesRepository;
    /** @var IUserRepository */
    private $userRepository;
    /** @var KeyService */
    private $keyService;
    /** @var Legacy */
    private $legacy;
    /** @var IUserStateRepository */
    private $userStateRepository;
    /** @var FileService */
    private $fileService;
    /** @var InstanceRepository $instanceRepository */
    private $instanceRepository;
    /** @var CredentialService */
    private $credentialService;
    /** @var IDateTimeService */
    private $dateTimeService;

    public function __construct(
        IApiLogRepository $apiLogRepository
        , IFileRepository $fileRepository
        , IEncryptionKeyRepository $keyRepository
        , IRoleRepository $rolesRepository
        , IUserRepository $userRepository
        , KeyService $keyService
        , Legacy $legacy
        , IUserStateRepository $userStateRepository
        , FileService $fileService
        , InstanceRepository $instanceRepository
        , CredentialService $credentialService
        , IDateTimeService $dateTimeService
    ) {
        $this->apiLogRepository    = $apiLogRepository;
        $this->fileRepository      = $fileRepository;
        $this->keyRepository       = $keyRepository;
        $this->rolesRepository     = $rolesRepository;
        $this->userRepository      = $userRepository;
        $this->keyService          = $keyService;
        $this->legacy              = $legacy;
        $this->userStateRepository = $userStateRepository;
        $this->fileService         = $fileService;
        $this->instanceRepository  = $instanceRepository;
        $this->credentialService   = $credentialService;
        $this->dateTimeService     = $dateTimeService;
    }

    public function removeUser(IUser $user): array {

        $logsRemoved  = $this->apiLogRepository->removeForUser($user);
        $filesRemoved = $this->fileRepository->removeForUser($user);
        $keysRemoved  = $this->keyRepository->remove($user);
        $rolesRemoved = $this->rolesRepository->removeUserRoles($user);
        $userRemoved  = $this->userRepository->remove($user);

        return [
            "logs_removed"    => $logsRemoved
            , "files_removed" => $filesRemoved
            , "keys_removed"  => $keysRemoved
            , "roles_removed" => $rolesRemoved
            , "user_removed"  => $user
            , "success"       =>
                true === $logsRemoved
                && true === $filesRemoved
                && true === $keysRemoved
                && true === $rolesRemoved
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

    public function hashUserId(IUser $user): string {
        return hash("sha256", (string) $user->getId());
    }

    /**
     * @return IUser
     * @TODO urgent: this user has to be locked out (no login possible)
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
        return $user;
    }

    public function getRandomHash(): string {
        return hash("sha256", uniqid("", true));
    }

    public function hashPassword(string $plain): string {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    /**
     * @param IUser      $user
     * @param bool       $lockUser
     * @param IFile|null $file
     *
     * @return bool
     * @throws KeyNotCreatedException
     * @throws UserNotCreatedException
     * @throws UserNotLockedException
     */
    public function createUser(
        IUser $user
        , bool $lockUser = false
        , ?IFile $file = null
    ): bool {

        $userId = $this->userRepository->insert($user);

        if (null === $userId) {
            throw new UserNotCreatedException();
        }

        $key = $this->keyService->createKey(
            $this->credentialService->getCredentialForUser($user)
            , $user
        );

        if (null === $key) {
            throw new KeyNotCreatedException("could not create key");
        }

        $stored = $this->keyService->storeKey($user, $key);

        if (false === $stored) {
            throw new KeyNotCreatedException("could not store key");
        }

        if (false === $lockUser) return true;

        $user->setId($userId);
        $locked = $this->userStateRepository->lock($user);

        if (false === $locked) {
            throw new UserNotLockedException();
        }

        if (null === $file) return true;

        $this->fileRepository->add($file);

        return true;
    }

    public function toUser(array $userArray): IUser {
        $user = new User();
        $user->setCreateTs(
            $this->dateTimeService->toDateTime((int) $userArray["create_ts"])
        );
        $user->setName($userArray["user_name"]);
        $user->setEmail($userArray["email"]);
        $user->setLastName($userArray["last_name"]);
        $user->setFirstName($userArray["first_name"]);
        $user->setPassword(
            $this->hashPassword($userArray["password"])
        );
        FileLogger::debug(json_encode($userArray));
        $user->setPhone($userArray["phone"]);
        $user->setWebsite($userArray["website"]);
        $user->setHash(
            $this->getRandomHash()
        );
        return $user;
    }

    /**
     * @param IUser $user
     *
     * @return bool
     * @throws KeyNotCreatedException
     * @throws UserNotCreatedException
     * @throws UserNotLockedException
     */
    public function createSystemUser(IUser $user): bool {
        $file = $this->fileService->defaultProfileImage();
        $file->setOwner($user);
        return $this->createUser(
            $user
            , true
            , $file
        );
    }

    public function isDisabled(?IUser $user): bool {
        return null === $user && true === $user->isLocked();
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

}

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
use Keestash;
use Keestash\Core\DTO\User;
use KSP\Core\DTO\IUser;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\IEncryptionKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Permission\IRoleRepository;
use KSP\Core\Repository\User\IUserRepository;

class UserService {

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

    public function __construct(
        IApiLogRepository $apiLogRepository
        , IFileRepository $fileRepository
        , IEncryptionKeyRepository $keyRepository
        , IPermissionRepository $rolesRepository
        , IUserRepository $userRepository
    ) {
        $this->apiLogRepository = $apiLogRepository;
        $this->fileRepository   = $fileRepository;
        $this->keyRepository    = $keyRepository;
        $this->rolesRepository  = $rolesRepository;
        $this->userRepository   = $userRepository;
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

    public function hashPassword(string $plain): string {
        return password_hash($plain, PASSWORD_BCRYPT);
    }

    public function passwordHasMinimumRequirements(string $password): bool {
        $passwordLength = strlen($password);

        if (true === $passwordLength < 8) return false;

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

    public function getRandomHash(): string {
        return hash("sha256", uniqid("", true));
    }

    /**
     * @return IUser
     * @TODO urgent: this user has to be locked out (no login possible)
     */
    public function getSystemUser(): IUser {
        $user = new User();
        $user->setName((string) Keestash::getServer()->getLegacy()->getApplication()->get("name"));
        $user->setId(IUser::SYSTEM_USER_ID);
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setCreateTs((new DateTime())->getTimestamp());
        $user->setEmail((string) Keestash::getServer()->getLegacy()->getApplication()->get("email"));
        $user->setFirstName((string) Keestash::getServer()->getLegacy()->getApplication()->get("name"));
        $user->setLastName((string) Keestash::getServer()->getLegacy()->getApplication()->get("name"));
        $user->setPhone((string) Keestash::getServer()->getLegacy()->getApplication()->get("phone"));
        $user->setWebsite((string) Keestash::getServer()->getLegacy()->getApplication()->get("web"));
        $user->setPassword(
            $this->hashPassword($user->getName())
        );
        return $user;
    }

}

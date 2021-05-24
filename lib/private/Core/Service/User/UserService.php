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
use Firebase\JWT\JWT;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\User\IUserService;

class UserService implements IUserService {

    private Legacy           $legacy;
    private IDateTimeService $dateTimeService;
    private HTTPService      $httpService;
    private InstanceDB       $instanceDB;

    public function __construct(
        Legacy $legacy
        , IDateTimeService $dateTimeService
        , HTTPService $httpService
        , InstanceDB $instanceDB
    ) {
        $this->legacy          = $legacy;
        $this->dateTimeService = $dateTimeService;
        $this->httpService     = $httpService;
        $this->instanceDB      = $instanceDB;
    }

    public function validatePassword(string $password, string $hash): bool {
        return true === password_verify($password, $hash);
    }

    public function passwordHasMinimumRequirements(string $password): bool {
        $passwordLength = strlen($password);

        if (true === $passwordLength < IUserService::MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD) return false;

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

    public function isDisabled(?IUser $user): bool {
        return null === $user || true === $user->isLocked();
    }

}

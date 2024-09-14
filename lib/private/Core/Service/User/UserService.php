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
use DateTimeImmutable;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\DI\Object\String\IStringService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash;
use Keestash\Core\DTO\RBAC\Role;
use Keestash\Core\DTO\User\User;
use Keestash\Core\System\Application;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;
use TypeError;

final readonly class UserService implements IUserService {

    public function __construct(
        private Application              $legacy
        , private IDateTimeService       $dateTimeService
        , private IStringService         $stringService
        , private IUserRepositoryService $userRepositoryService
        , private EmailValidator         $emailValidator
        , private PhoneValidator         $phoneValidator
        , private UriValidator           $uriValidator
        , private ILocaleService         $localeService
        , private ILanguageService       $languageService
        , private Config                 $config
    ) {
    }

    #[\Override]
    public function verifyPassword(string $password, string $hash): bool {
        return true === password_verify($password, $hash);
    }

    #[\Override]
    public function passwordHasMinimumRequirements(string $password): bool {
        $passwordLength = strlen($password);

        if (true === $passwordLength < IUserService::MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD) return false;

        // minimum 1 number
        /** @phpstan-ignore-next-line */
        if (strlen((string) preg_replace('/([^0-9]*)/', '', $password)) < 1) return false;

        /** @phpstan-ignore-next-line */
        if (strlen((string) preg_replace('/([^a-zA-Z]*)/', '', $password)) < 1) return false;

        // Check the number of lower case letters in the password
        /** @phpstan-ignore-next-line */
        if (strlen((string) preg_replace('/([^a-z]*)/', '', $password)) < 1) return false;

        // Check the number of upper case letters in the password
        /** @phpstan-ignore-next-line */
        if (strlen((string) preg_replace('/([^A-Z]*)/', '', $password)) < 1) return false;

        // Check the minimum number of symbols in the password.
        /** @phpstan-ignore-next-line */
        if (strlen((string) preg_replace('/([a-zA-Z0-9]*)/', '', $password)) < 1) return false;

        return true;
    }

    #[\Override]
    public function validEmail(string $email): bool {
        return false !== filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    #[\Override]
    public function validWebsite(string $email): bool {
        return false !== filter_var($email, FILTER_VALIDATE_URL);
    }

    /**
     * @return IUser
     */
    #[\Override]
    public function getSystemUser(): IUser {
        $user = new User();
        $user->setName((string) $this->legacy->getMetaData()->get("name"));
        $user->setId(IUser::SYSTEM_USER_ID);
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setCreateTs(new DateTime());
        $user->setEmail((string) $this->legacy->getMetaData()->get("email"));
        $user->setFirstName((string) $this->legacy->getMetaData()->get("name"));
        $user->setLastName((string) $this->legacy->getMetaData()->get("name"));
        $user->setPhone((string) $this->legacy->getMetaData()->get("phone"));
        $user->setWebsite((string) $this->legacy->getMetaData()->get("web"));
        $user->setLocale(
            $this->localeService->getLocale()
        );
        $user->setLanguage(
            $this->languageService->getLanguage()
        );
        $user->setPassword(
            $this->hashPassword($user->getName())
        );
        $user->setLocked(true);
        $user->setRoles(new HashTable());
        return $user;
    }

    /**
     * @return IUser
     */
    #[\Override]
    public function getDemoUser(): IUser {
        $user = new User();
        $user->setName(IUser::DEMO_USER_NAME);
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setCreateTs(new DateTime());
        $user->setEmail((string) $this->legacy->getMetaData()->get("email"));
        $user->setFirstName((string) $this->legacy->getMetaData()->get("name"));
        $user->setLastName((string) $this->legacy->getMetaData()->get("name"));
        $user->setPhone((string) $this->legacy->getMetaData()->get("phone"));
        $user->setWebsite((string) $this->legacy->getMetaData()->get("web"));
        $user->setLocale(
            $this->localeService->getLocale()
        );
        $user->setLanguage(
            $this->languageService->getLanguage()
        );
        $user->setPassword(
            $this->hashPassword(IUser::DEMO_USER_NAME)
        );
        $user->setRoles(new HashTable());
        return $user;
    }

    #[\Override]
    public function getRandomHash(): string {
        return hash("sha256", uniqid("", true));
    }

    #[\Override]
    public function hashPassword(string $plain): string {
        /** @var string|false|null $hashed */
        $hashed = password_hash($plain, PASSWORD_BCRYPT);
        if (false === is_string($hashed)) {
            throw new KeestashException();
        }
        return $hashed;
    }

    /**
     * @param array $userArray
     * @return IUser
     * @throws TypeError
     */
    #[\Override]
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
        $user->setPassword($userArray['password'] ?? IUser::VERY_DUMB_ATTEMPT_TO_MOCK_PASSWORDS_ON_SYSTEM_LEVEL_BUT_SECURITY_GOES_FIRST);
        $user->setPhone($userArray['phone']);
        $user->setWebsite($userArray['website']);
        $user->setLanguage($userArray['language']);
        $user->setLocale($userArray['locale']);

        $roles = new HashTable();
        foreach (($userArray['roles'] ?? []) as $role) {
            $roles->put(
                (int) $role['id']
                , new Role(
                    (int) $role['id']
                    , $role['name']
                    , new HashTable()
                    , new DateTimeImmutable()
                )
            );
        }
        $user->setRoles($roles);
        return $user;
    }

    #[\Override]
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
        $user->setLocked($userArray['locked'] ?? false);
        $user->setDeleted($userArray['deleted'] ?? false);
        $user->setLocale(
            $this->localeService->getLocale()
        );
        $user->setLanguage(
            $this->languageService->getLanguage()
        );
        $user->setHash(
            $this->getRandomHash()
        );
        $user->setLdapUser($userArray['ldapUser'] ?? false);
        $roles = new HashTable();
        foreach (($userArray['roles'] ?? []) as $key => $role) {
            $roles->put(
                $role['id']
                , new Role(
                    $role['id']
                    , $role['name']
                    , new HashTable()
                    , new DateTimeImmutable()
                )
            );
        }
        $user->setRoles($roles);
        return $user;
    }

    /**
     * @param string $password
     * @param string $passwordRepeat
     * @return ArrayList
     */
    #[\Override]
    public function validatePasswords(string $password, string $passwordRepeat): ArrayList {
        $resultList = new ArrayList();
        if (true === $this->stringService->isEmpty($password)) {
            $resultList->add('PASSWORD_IS_EMPTY');
            return $resultList;
        }

        if (true === $this->stringService->isEmpty($passwordRepeat)) {
            $resultList->add('PASSWORD_REPEAT_IS_EMPTY');
            return $resultList;
        }

        if (false === $this->stringService->equals($password, $passwordRepeat)) {
            $resultList->add('PASSWORD_AND_PASSWORD_REPEAT_ARE_NOT_EQUAL');
        }

        if (false === $this->passwordHasMinimumRequirements($password)) {
            $resultList->add('PASSWORD_MINIMUM_REQUIREMENTS_ARE_NOT_MET');
        }
        return $resultList;
    }

    /**
     * @param IUser $user
     * @return ArrayList
     */
    #[\Override]
    public function validateNewUser(IUser $user): ArrayList {
        $result = new ArrayList();
        if (true === $this->stringService->isEmpty($user->getFirstName())) {
            $result->add('INVALID_FIRST_NAME');
        }

        if (true === $this->stringService->isEmpty($user->getLastName())) {
            $result->add('INVALID_LAST_NAME');
        }

        if (true === $this->stringService->isEmpty($user->getName())) {
            $result->add('INVALID_USER_NAME');
        }

        if (true === $this->userRepositoryService->userExistsByName($user->getName())) {
            $result->add('USER_NAME_EXISTS');
        }

        if (true === $this->userRepositoryService->userExistsByEmail($user->getEmail())) {
            $result->add('EMAIL_EXISTS');
        }

        if (
            false === $this->emailValidator->isValid($user->getEmail())
            || false === filter_var($user->getEmail(), FILTER_VALIDATE_EMAIL)
        ) {
            $result->add('EMAIL_ADDRESS_IS_INVALID');
        }

        if (false === $this->validateWithAllCountries($user->getPhone())) {
            $result->add('INVALID_PHONE');
        }

        if (false === $this->uriValidator->isValid($user->getWebsite())) {
            $result->add('INVALID_WEBSITE');
        }

        return $result;
    }

    public function validateWithAllCountries(string $phone): bool {
        $countryCodes = $this->config->get(Keestash\ConfigProvider::COUNTRY_CODES)->toArray();
        foreach ($countryCodes as $countryCode) {
            $this->phoneValidator->setCountry((string) $countryCode);
            if ($this->phoneValidator->isValid($phone)) {
                return true;
            }
        }
        return false;
    }

    #[\Override]
    public function isDisabled(?IUser $user): bool {
        return null === $user || true === $user->isLocked();
    }

    #[\Override]
    public function isSystemUser(IUser $user): bool {
        return $user->getId() === IUser::SYSTEM_USER_ID;
    }

}

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
use doganoo\DI\Object\String\IStringService;
use Keestash;
use Keestash\Core\DTO\User\User;
use Keestash\Exception\KeestashException;
use Keestash\Legacy\Legacy;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Config\Config;
use Laminas\I18n\Validator\PhoneNumber as PhoneValidator;
use Laminas\Validator\EmailAddress as EmailValidator;
use Laminas\Validator\Uri as UriValidator;

class UserService implements IUserService {

    private Legacy                 $legacy;
    private IDateTimeService       $dateTimeService;
    private IStringService         $stringService;
    private IUserRepositoryService $userRepositoryService;
    private EmailValidator         $emailValidator;
    private PhoneValidator         $phoneValidator;
    private UriValidator           $uriValidator;
    private ILocaleService         $localeService;
    private ILanguageService       $languageService;
    private Config                 $config;

    public function __construct(
        Legacy                   $legacy
        , IDateTimeService       $dateTimeService
        , IStringService         $stringService
        , IUserRepositoryService $userRepositoryService
        , EmailValidator         $emailValidator
        , PhoneValidator         $phoneValidator
        , UriValidator           $uriValidator
        , ILocaleService         $localeService
        , ILanguageService       $languageService
        , Config                 $config
    ) {
        $this->legacy                = $legacy;
        $this->dateTimeService       = $dateTimeService;
        $this->stringService         = $stringService;
        $this->userRepositoryService = $userRepositoryService;
        $this->emailValidator        = $emailValidator;
        $this->phoneValidator        = $phoneValidator;
        $this->uriValidator          = $uriValidator;
        $this->languageService       = $languageService;
        $this->localeService         = $localeService;
        $this->config                = $config;
    }

    public function verifyPassword(string $password, string $hash): bool {
        return true === password_verify($password, $hash);
    }

    public function passwordHasMinimumRequirements(string $password): bool {
        $passwordLength = strlen($password);

        if (true === $passwordLength < IUserService::MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD) return false;

        // minimum 1 number
        /** @phpstan-ignore-next-line */
        if (strlen(preg_replace('/([^0-9]*)/', '', $password)) < 1) return false;

        /** @phpstan-ignore-next-line */
        if (strlen(preg_replace('/([^a-zA-Z]*)/', '', $password)) < 1) return false;

        // Check the number of lower case letters in the password
        /** @phpstan-ignore-next-line */
        if (strlen(preg_replace('/([^a-z]*)/', '', $password)) < 1) return false;

        // Check the number of upper case letters in the password
        /** @phpstan-ignore-next-line */
        if (strlen(preg_replace('/([^A-Z]*)/', '', $password)) < 1) return false;

        // Check the minimum number of symbols in the password.
        /** @phpstan-ignore-next-line */
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
        $user->setLocale(
            $this->localeService->getLocale()
        );
        $user->setLanguage(
            $this->languageService->getLanguage()
        );
        $user->setPassword(
            $this->hashPassword(IUser::DEMO_USER_NAME)
        );
        return $user;
    }

    public function getRandomHash(): string {
        return hash("sha256", uniqid("", true));
    }

    public function hashPassword(string $plain): string {
        $hashed = password_hash($plain, PASSWORD_BCRYPT);
        if (false === is_string($hashed)) {
            throw new KeestashException();
        }
        return $hashed;
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
        $user->setLanguage($userArray['language']);
        $user->setLocale($userArray['locale']);
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
        return $user;
    }

    /**
     * @param string $password
     * @param string $passwordRepeat
     * @return void
     * @throws KeestashException
     */
    public function validatePasswords(string $password, string $passwordRepeat): void {
        if (true === $this->stringService->isEmpty($password)) {
            throw new KeestashException('password is empty');
        }

        if (true === $this->stringService->isEmpty($passwordRepeat)) {
            throw new KeestashException('password repeat is empty');
        }

        if (false === $this->stringService->equals($password, $passwordRepeat)) {
            throw new KeestashException('password and password repeat are not equal');
        }

        if (false === $this->passwordHasMinimumRequirements($password)) {
            throw new KeestashException('password and password repeat are not equal');
        }
    }

    /**
     * @param IUser $user
     * @return IUser
     * @throws KeestashException
     */
    public function validateNewUser(IUser $user): IUser {

        if (true === $this->stringService->isEmpty($user->getFirstName())) {
            throw new KeestashException('invalid first name');
        }

        if (true === $this->stringService->isEmpty($user->getLastName())) {
            throw new KeestashException('invalid last name');
        }

        if (true === $this->stringService->isEmpty($user->getName())) {
            throw new KeestashException('invalid name name');
        }

        if (true === $this->userRepositoryService->userExistsByName($user->getName())) {
            throw new KeestashException('name exists');
        }

        if (true === $this->userRepositoryService->userExistsByEmail($user->getEmail())) {
            throw new KeestashException('mail exists');
        }

        if (false === $this->emailValidator->isValid($user->getEmail())) {
            throw new KeestashException('invalid email address');
        }

        $this->phoneValidator->setOptions(['country' => $user->getLocale()]);
        if (false === $this->validateWithAllCountries($user->getPhone())) {
            throw new KeestashException('invalid phone');
        }

        if (false === $this->uriValidator->isValid($user->getWebsite())) {
            throw new KeestashException('invalid website');
        }

        return $user;
    }

    public function validateWithAllCountries(string $phone): bool {
        $countryCodes = $this->config->get(Keestash\ConfigProvider::COUNTRY_CODES);
        foreach ($countryCodes as $countryCode) {
            $this->phoneValidator->setCountry($countryCode);
            if ($this->phoneValidator->isValid($phone)) {
                return true;
            }
        }
        return false;
    }

    public function isDisabled(?IUser $user): bool {
        return null === $user || true === $user->isLocked();
    }

}

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

namespace KST\Service\Service;

use DateTime;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Core\DTO\User\User;
use Keestash\Legacy\Legacy;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;

class UserService {

    public const TEST_USER_ID_2        = 2;
    public const TEST_USER_ID_3        = 3;
    public const TEST_LOCKED_USER_ID_4 = 4;

    private array $userData = [
        [
            'id'         => self::TEST_USER_ID_2
            , 'name'     => 'TestUser2'
            , 'hash'     => self::TEST_USER_ID_2
            , 'password' => ''
            , 'locked'   => false
        ],
        [
            'id'         => self::TEST_USER_ID_3
            , 'name'     => 'TestUser3'
            , 'hash'     => self::TEST_USER_ID_3
            , 'password' => ''
            , 'locked'   => false
        ],
        [
            'id'         => self::TEST_LOCKED_USER_ID_4
            , 'name'     => 'TestUser4'
            , 'hash'     => self::TEST_LOCKED_USER_ID_4
            , 'password' => ''
            , 'locked'   => true
        ]
    ];

    private Legacy                 $legacy;
    private IUserRepositoryService $userRepositoryService;
    private IUserService           $userService;
    private ILocaleService         $localeService;
    private ILanguageService       $languageService;

    public function __construct(
        Legacy                   $legacy
        , IUserRepositoryService $userRepositoryService
        , IUserService           $userService
        , ILocaleService         $localeService
        , ILanguageService       $languageService
    ) {
        $this->legacy                = $legacy;
        $this->userRepositoryService = $userRepositoryService;
        $this->userService           = $userService;
        $this->languageService       = $languageService;
        $this->localeService         = $localeService;
    }

    public function createTestUsers(): void {
        $this->userRepositoryService->createSystemUser(
            $this->userService->getSystemUser()
        );
        foreach ($this->userData as $data) {
            $user = new User();
            $user->setName($data['name']);
            $user->setId($data['id']);
            $user->setHash(md5((string) $data['hash']));
            $user->setCreateTs(new DateTime());
            $user->setEmail((string) $this->legacy->getApplication()->get("email"));
            $user->setFirstName((string) $this->legacy->getApplication()->get("name"));
            $user->setLastName((string) $this->legacy->getApplication()->get("name"));
            $user->setPhone((string) $this->legacy->getApplication()->get("phone"));
            $user->setWebsite((string) $this->legacy->getApplication()->get("web"));
            $user->setPassword($data['password']);
            $user->setLocked($data['locked']);
            $user->setLocale(
                $this->localeService->getLocale()
            );
            $user->setLanguage(
                $this->languageService->getLanguage()
            );
            $user->setRoles(new HashTable());
            $this->userRepositoryService->createUser($user);
        }
    }

}
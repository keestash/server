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

use DateTimeImmutable;
use Keestash\Core\System\Application;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Ramsey\Uuid\Uuid;

class UserService {

    public const TEST_USER_ID_2          = 2;
    public const TEST_USER_ID_2_NAME     = 'TestUser2';
    public const TEST_USER_ID_2_PASSWORD = 'ec1ce427dde12ca6e4f7c2853dce60ba';
    public const TEST_USER_ID_2_EMAIL    = '82e92302cbe5ea70f9d4e24f740c58f8@keestash.com';

    public const TEST_USER_ID_3          = 3;
    public const TEST_USER_ID_3_NAME     = 'TestUser3';
    public const TEST_USER_ID_3_PASSWORD = 'a7bf5114650d3b43a5db14088251570c';
    public const TEST_USER_ID_3_EMAIL    = 'fd7eea1db9b486aedd38f607484d4f3e@keestash.com';

    public const TEST_LOCKED_USER_ID_4          = 4;
    public const TEST_LOCKED_USER_ID_4_NAME     = 'TestUser4';
    public const TEST_LOCKED_USER_ID_4_PASSWORD = '35718b4a649d8a1f303b5be19e00c301';
    public const TEST_LOCKED_USER_ID_4_EMAIL    = '82806459c099e7e1ccfe709a3c5e0548@keestash.com';

    public const TEST_PASSWORD_RESET_USER_ID_5          = 5;
    public const TEST_PASSWORD_RESET_USER_ID_5_NAME     = 'TestUser5';
    public const TEST_PASSWORD_RESET_USER_ID_5_PASSWORD = '368ef001d16adc04a76ff6266b1ce0f6';
    public const TEST_PASSWORD_RESET_USER_ID_5_EMAIL    = '5af9f426673ee8fdab6959e685557520@keestash.com';

    public const TEST_PASSWORD_FORGOT_USER_ID_6          = 6;
    public const TEST_PASSWORD_FORGOT_USER_ID_6_NAME     = 'TestUser6';
    public const TEST_PASSWORD_FORGOT_USER_ID_6_PASSWORD = 'e4ab34b63a6f671a69a06623bf57c258';
    public const TEST_PASSWORD_FORGOT_USER_ID_6_EMAIL    = 'c02864952954089b844239e969b1eb14@keestash.com';

    public const TEST_RESET_PASSWORD_USER_ID_7           = 7;
    public const TEST_RESET_PASSWORD_USER_ID_7_NAME      = 'TestUser7';
    public const TEST_PASSWORD_FORGOT_USER_ID_7_PASSWORD = 'a9e80d53c775abec87b8fbffa306b79c';
    public const TEST_PASSWORD_FORGOT_USER_ID_7_EMAIL    = 'bcf5cfb513064cf4f2859ee84e1d3bea@keestash.com';

    private array $userData = [
        [
            'id'         => UserService::TEST_USER_ID_2
            , 'name'     => UserService::TEST_USER_ID_2_NAME
            , 'password' => UserService::TEST_USER_ID_2_PASSWORD
            , 'email'    => UserService::TEST_USER_ID_2_EMAIL
            , 'locked'   => false
        ],
        [
            'id'         => UserService::TEST_USER_ID_3
            , 'name'     => UserService::TEST_USER_ID_3_NAME
            , 'password' => UserService::TEST_USER_ID_3_PASSWORD
            , 'email'    => UserService::TEST_USER_ID_3_EMAIL
            , 'locked'   => false
        ],
        [
            'id'         => UserService::TEST_LOCKED_USER_ID_4
            , 'name'     => UserService::TEST_LOCKED_USER_ID_4_NAME
            , 'password' => UserService::TEST_LOCKED_USER_ID_4_PASSWORD
            , 'email'    => UserService::TEST_LOCKED_USER_ID_4_EMAIL
            , 'locked'   => true
        ],
        [
            'id'         => UserService::TEST_PASSWORD_RESET_USER_ID_5
            , 'name'     => UserService::TEST_PASSWORD_RESET_USER_ID_5_NAME
            , 'password' => UserService::TEST_PASSWORD_RESET_USER_ID_5_PASSWORD
            , 'email'    => UserService::TEST_PASSWORD_RESET_USER_ID_5_EMAIL
            , 'locked'   => false
        ],
        [
            'id'         => UserService::TEST_PASSWORD_FORGOT_USER_ID_6
            , 'name'     => UserService::TEST_PASSWORD_FORGOT_USER_ID_6_NAME
            , 'password' => UserService::TEST_PASSWORD_FORGOT_USER_ID_6_PASSWORD
            , 'email'    => UserService::TEST_PASSWORD_FORGOT_USER_ID_6_EMAIL
            , 'locked'   => false
        ],
        [
            'id'         => UserService::TEST_RESET_PASSWORD_USER_ID_7
            , 'name'     => UserService::TEST_RESET_PASSWORD_USER_ID_7_NAME
            , 'password' => UserService::TEST_PASSWORD_FORGOT_USER_ID_7_PASSWORD
            , 'email'    => UserService::TEST_PASSWORD_FORGOT_USER_ID_7_EMAIL
            , 'locked'   => false
        ]
    ];

    private Application            $legacy;
    private IUserRepositoryService $userRepositoryService;
    private IUserService           $userService;
    private ILocaleService         $localeService;
    private ILanguageService       $languageService;

    public function __construct(
        Application                             $legacy
        , IUserRepositoryService                $userRepositoryService
        , IUserService                          $userService
        , ILocaleService                        $localeService
        , ILanguageService                      $languageService
        , private readonly IEventService        $eventService
        , private readonly IUserStateRepository $userStateRepository
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
            $data['create_ts']  = json_decode(
                json_encode(new DateTimeImmutable(), JSON_THROW_ON_ERROR)
                , true
            );
            $data['deleted']    = false;
            $data['user_name']  = $data['name'];
            $data['first_name'] = (string) $this->legacy->getMetaData()->get("name");
            $data['last_name']  = (string) $this->legacy->getMetaData()->get("name");
            $data['hash']       = Uuid::uuid4()->toString();
            $data['phone']      = (string) $this->legacy->getMetaData()->get("phone");
            $data['website']    = (string) $this->legacy->getMetaData()->get("web");
            $data['language']   = $this->languageService->getLanguage();
            $data['locale']     = $this->localeService->getLocale();
            $user               = $this->userService->toNewUser($data);
            $user               = $this->userRepositoryService->createUser($user);
            $this->eventService->execute(
                new UserRegistrationConfirmedEvent(
                    $user
                )
            );
            if (false === $data['locked'] && $user->isLocked()) {
                $this->userStateRepository->unlock($user);
            }
        }
    }

}
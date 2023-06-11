<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\Register\Test\Integration\Api\User;

use DateTimeImmutable;
use Keestash\Core\DTO\LDAP\LDAPOption;
use Keestash\Core\Service\App\LoaderService;
use KSA\Register\Api\User\Add;
use KSA\Register\ConfigProvider;
use KSA\Register\Test\Integration\TestCase;
use KSA\Settings\Entity\Setting;
use KSA\Settings\Exception\SettingsException;
use KSA\Settings\Repository\SettingsRepository;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Ramsey\Uuid\Uuid;

class AddTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var Add $add */
        $add = $this->getService(Add::class);

        $response = $add->handle(
            $this->getVirtualRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithInvalidPassword(): void {
        /** @var Add $add */
        $add = $this->getService(Add::class);

        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => Uuid::uuid4()->toString()
                    , 'email'                => Uuid::uuid4() . '@keestash.com'
                    , 'password'             => ''
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithInvalidWebsite(): void {
        /** @var Add $add */
        $add = $this->getService(Add::class);

        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => AddTest::class
                    , 'email'                => 'dev.null.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'phone'                => '0049691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithInvalidPhone(): void {
        /** @var Add $add */
        $add = $this->getService(Add::class);

        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'first_name'             => AddTest::class
                    , 'last_name'            => AddTest::class
                    , 'user_name'            => AddTest::class
                    , 'email'                => Uuid::uuid4() . '@keestash.com'
                    , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                    , 'phone'                => '1e9691234566'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        /** @var IUserRepositoryService $userRepositoryService */
        $userRepositoryService = $this->getService(IUserRepositoryService::class);
        /** @var SettingsRepository $settingRepository */
        $settingRepository = $this->getService(SettingsRepository::class);
        $settingRepository->remove(
            LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
        );

        $firstName = md5((string) time());
        $lastName  = md5((string) (time() + 1));
        $userName  = md5((string) (time() + 2));

        /** @var Add $add */
        $add      = $this->getService(Add::class);
        $password = '1E]U_t"0Xh&}gtTPA`|?';
        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'first_name'             => $firstName
                    , 'last_name'            => $lastName
                    , 'user_name'            => $userName
                    , 'email'                => Uuid::uuid4() . '@keestash.com'
                    , 'password'             => $password
                    , 'password_repeat'      => $password
                    , 'phone'                => '004930123456'
                    , 'terms_and_conditions' => true
                    , 'website'              => 'keestash.com'
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));

        $user = $userRepository->getUser($userName);
        $userRepositoryService->removeUser($user);
    }

    public function testWithDisabledApp(): void {
        /** @var SettingsRepository $settingRepository */
        $settingRepository = $this->getService(SettingsRepository::class);
        try {
            $restrictLocalAccounts = $settingRepository->get(
                LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
            );
            $settingRepository->remove(
                LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
            );
        } catch (SettingsException) {
            $restrictLocalAccounts = null;
        }
        $settingRepository->add(
            new Setting(
                LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
                , 'true'
                , new DateTimeImmutable()
            )
        );

        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        // no need a payload as it should stop before validating
                    ]
                )
            );

        $data = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertArrayHasKey(0, $data);
        $this->assertTrue($data[0] === 'unknown operation');
        $settingRepository->remove(
            LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
        );
        $settingRepository->remove(
            LDAPOption::RESTRICT_LOCAL_ACCOUNTS->value
        );
        if (null !== $restrictLocalAccounts) {
            $settingRepository->add($restrictLocalAccounts);
        }
    }

}
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
use JsonException;
use Keestash\Core\DTO\LDAP\LDAPOption;
use Keestash\Exception\KeestashException;
use KSA\Register\ConfigProvider;
use KSA\Register\Entity\IResponseCodes;
use KSA\Register\Test\Integration\TestCase;
use KSA\Settings\Entity\Setting;
use KSA\Settings\Exception\SettingsException;
use KSA\Settings\Repository\SettingsRepository;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class AddTest extends TestCase {

    /**
     * @return void
     * @throws JsonException
     * @throws KeestashException
     */
    public function testEmptyBody(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , []
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame([
            'responseCode' => IResponseCodes::RESPONSE_CODE_TERMS_AND_CONDITIONS_NOT_AGREED
        ], $decoded);
    }

    public function testEmptyPassword(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'password'               => ''
                        , 'phone'                => '0049691234566'
                        , 'terms_and_conditions' => true
                        , 'website'              => 'keestash.com'
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame([
            'responseCode' => IResponseCodes::RESPONSE_CODE_INVALID_PASSWORD,
            'results'      => [
                0 => 'PASSWORD_IS_EMPTY'
            ]
        ], $decoded);
    }

    public function testPasswordsDoNotMatch(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'email'                  => 'dev.null.com'
                        , 'password'             => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => 'sgdfgsfdfasd'
                        , 'phone'                => '0049691234566'
                        , 'terms_and_conditions' => true
                        , 'website'              => 'keestash.com'
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame([
            'responseCode' => IResponseCodes::RESPONSE_CODE_INVALID_PASSWORD,
            'results'      => [
                0 => 'PASSWORD_AND_PASSWORD_REPEAT_ARE_NOT_EQUAL'
            ]
        ], $decoded);
    }

    public function testUserIsCreated(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'first_name'           => Uuid::uuid4()->toString(),
                        'last_name'            => Uuid::uuid4()->toString(),
                        'email'                => Uuid::uuid4()->toString() . '@keestash.com',
                        'user_name'            => Uuid::uuid4()->toString(),
                        'password'             => '1E]U_t"0Xh&}gtTPA`|?',
                        'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?',
                        'phone'                => '1e9691234566',
                        'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertSame(['responseCode' => IResponseCodes::RESPONSE_CODE_USER_CREATED,], $decoded);
    }

    public function testInvalidFirstName(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'first_name'             => '',
                        'last_name'              => Uuid::uuid4()->toString(),
                        'email'                  => Uuid::uuid4()->toString() . '@keestash.com',
                        'user_name'              => Uuid::uuid4()->toString(),
                        'password'               => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'phone'                => '1e9691234566'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame(
            [
                'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
                'results'      => [
                    0 => 'INVALID_FIRST_NAME'
                ]
            ],
            $decoded
        );
    }

    public function testInvalidLastName(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'last_name'              => '',
                        'first_name'             => Uuid::uuid4()->toString(),
                        'email'                  => Uuid::uuid4()->toString() . '@keestash.com',
                        'user_name'              => Uuid::uuid4()->toString(),
                        'password'               => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'phone'                => '1e9691234566'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame(
            [
                'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
                'results'      => [
                    0 => 'INVALID_LAST_NAME'
                ]
            ],
            $decoded
        );
    }

    public function testInvalidUserName(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'user_name'              => '',
                        'first_name'             => Uuid::uuid4()->toString(),
                        'email'                  => Uuid::uuid4()->toString() . '@keestash.com',
                        'last_name'              => Uuid::uuid4()->toString(),
                        'password'               => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'phone'                => '1e9691234566'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame(
            [
                'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
                'results'      => [
                    0 => 'INVALID_USER_NAME'
                ]
            ],
            $decoded
        );
    }

    public function testInvalidEmailAddress(): void {
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'first_name'             => Uuid::uuid4()->toString(),
                        'last_name'              => Uuid::uuid4()->toString(),
                        'email'                  => '',
                        'user_name'              => Uuid::uuid4()->toString(),
                        'password'               => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'phone'                => '1e9691234566'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame(
            [
                'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
                'results'      => [
                    0 => 'EMAIL_ADDRESS_IS_INVALID'
                ]
            ],
            $decoded
        );
    }

    public function testInvalidPhoneNumber(): void {
        $this->markTestSkipped('phone is hardcoded - test once fixed');
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'first_name'             => Uuid::uuid4()->toString(),
                        'last_name'              => Uuid::uuid4()->toString(),
                        'email'                  => Uuid::uuid4()->toString() . '@keestash.com',
                        'user_name'              => Uuid::uuid4()->toString(),
                        'password'               => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'password_repeat'      => '1E]U_t"0Xh&}gtTPA`|?'
                        , 'phone'                => 'adfasdfsadfsadfsdfdsfsdf'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame(
            [
                'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
                'results'      => [
                    0 => 'INVALID_PHONE'
                ]
            ],
            $decoded
        );
    }

    public function testExistingUser(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString()
        );

        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'user_name'              => $user->getName()
                        , 'email'                => Uuid::uuid4() . '@keestash.com'
                        , 'last_name'            => Uuid::uuid4()->toString()
                        , 'first_name'           => Uuid::uuid4()->toString()
                        , 'password'             => $user->getPassword()
                        , 'password_repeat'      => $user->getPassword()
                        , 'phone'                => '004913456773'
                        , 'website'              => 'keestash.com'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame([
            'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
            'results'      => [
                0 => 'USER_NAME_EXISTS'
            ]
        ], $decoded);

        $this->removeUser($user);
    }

    public function testExistingEmail(): void {
        $email = Uuid::uuid4() . '@keestash.com';
        $user  = $this->createUser(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            false,
            $email
        );

        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::REGISTER_ADD
                    , [
                        'user_name'              => Uuid::uuid4()->toString()
                        , 'email'                => $user->getEmail()
                        , 'last_name'            => Uuid::uuid4()->toString()
                        , 'first_name'           => Uuid::uuid4()->toString()
                        , 'password'             => $user->getPassword()
                        , 'password_repeat'      => $user->getPassword()
                        , 'phone'                => '004913456773'
                        , 'website'              => 'keestash.com'
                        , 'terms_and_conditions' => true
                    ]
                )
            );
        $decoded  = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->assertSame([
            'responseCode' => IResponseCodes::RESPONSE_CODE_VALIDATE_USER,
            'results'      => [
                0 => 'EMAIL_EXISTS'
            ]
        ], $decoded);

        $this->removeUser($user);
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
        $this->assertArrayHasKey('responseCode', $data);
        $this->assertTrue($data['responseCode'] === IResponseCodes::RESPONSE_CODE_REGISTER_DISABLED);
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

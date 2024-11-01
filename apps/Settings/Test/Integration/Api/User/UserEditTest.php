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

namespace KSA\Settings\Test\Integration\Api\User;

use JsonException;
use Keestash\Exception\KeestashException;
use KSA\Settings\Api\User\UserEdit;
use KSA\Settings\ConfigProvider;
use KSA\Settings\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\Repository\User\IUserRepository;
use KST\Service\Exception\KSTException;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class UserEditTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);

        $response = $userEdit->handle(
            $this->getVirtualRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithNoPermission(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);

        $response = $userEdit->handle(
            $this->getVirtualRequest(
                [
                    'user' => ['id' => UserService::TEST_PASSWORD_RESET_USER_ID_5]
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testWithMissingData(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_EDIT
                    , [
                        'user' => [
                            'id'           => $user->getId()
                            , 'first_name' => UserEdit::class
                        ]
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testRegularCase(): void {
        /** @var UserEdit $userEdit */
        $userEdit = $this->getService(UserEdit::class);
        /** @var IUserRepository $userRepository */
        $userRepository = $this->getService(IUserRepository::class);
        $user           = $userRepository->getUserById((string) UserService::TEST_USER_ID_2);
        $response       = $userEdit->handle(
            $this->getVirtualRequest(
                [
                    'user' => [
                        'id'           => $user->getId()
                        , 'name'       => $user->getName()
                        , 'first_name' => $user->getFirstName()
                        , 'last_name'  => $user->getLastName()
                        , 'email'      => $user->getEmail()
                        , 'phone'      => $user->getPhone()
                        , 'locked'     => $user->isLocked()
                        , 'deleted'    => $user->isDeleted()
                        , 'language'   => $user->getLanguage()
                        , 'locale'     => "{$user->getLanguage()}_{$user->getLocale()}"
                    ]
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
    }

    /**
     * @param string|null $language
     * @param string|null $locale
     * @return void
     * @throws JsonException
     * @throws KSTException
     * @throws KeestashException
     * @dataProvider provideInvalidLanguageAndLocale
     */
    public function testWithInvalidLocaleAndLanguageCode(?string $language, ?string $locale): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $payload = [
            'id'           => $user->getId()
            , 'name'       => Uuid::uuid4()->toString()
            , 'first_name' => $user->getFirstName()
            , 'last_name'  => $user->getLastName()
            , 'email'      => $user->getEmail()
            , 'phone'      => $user->getPhone()
            , 'locked'     => $user->isLocked()
            , 'deleted'    => $user->isDeleted()
        ];

        if (null !== $language) {
            $payload['language'] = $language;
        }

        if (null !== $locale) {
            $payload['locale'] = $locale;
        }

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_EDIT
                    , [
                        'user' => $payload
                    ]
                    , $user
                    , $headers
                )
            );

        $data = $this->getDecodedData($response);
        $this->assertStatusCode(IResponse::OK, $response);
        $this->assertArrayHasKey('user', $data);
        $this->assertArrayHasKey('languageUpdated', $data);
        $this->assertTrue(false === $data['languageUpdated']);
    }

    public static function provideInvalidLanguageAndLocale(): array {
        return [
            ['', ''],
            [null, null],
        ];
    }

}

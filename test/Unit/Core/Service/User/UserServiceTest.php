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

namespace KST\Unit\Core\Service\User;

use DateTimeImmutable;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\User\IUserService;
use KST\TestCase;
use Ramsey\Uuid\Uuid;

class UserServiceTest extends TestCase {

    private IUserService $userService;

    protected function setUp(): void {
        parent::setUp();
        $this->userService = $this->getService(IUserService::class);
    }

    public function testHashAndVerifyPassword(): void {
        $plain    = md5((string) time());
        $hashed   = $this->userService->hashPassword($plain);
        $verified = $this->userService->verifyPassword($plain, $hashed);
        $this->assertTrue(true === $verified);
    }

    /**
     * @param string $password
     * @param bool   $valid
     * @return void
     * @dataProvider provideMinimumRequirementsPasswords
     */
    public function testPasswordMinimumRequirements(string $password, bool $valid): void {
        $this->assertTrue(
            $valid === $this->userService->passwordHasMinimumRequirements($password)
        );
    }

    /**
     * @param string $email
     * @param bool   $valid
     * @return void
     * @dataProvider provideEmailAddress
     */
    public function testValidEmail(string $email, bool $valid): void {
        $this->assertTrue(
            $valid === $this->userService->validEmail($email)
        );
    }

    /**
     * @param string $email
     * @param bool   $valid
     * @return void
     * @dataProvider provideWebsite
     */
    public function testValidWebsite(string $email, bool $valid): void {
        $this->assertTrue(
            $valid === $this->userService->validWebsite($email)
        );
    }

    public function testGetSystemUser(): void {
        $this->assertTrue(
            IUser::SYSTEM_USER_ID === $this->userService->getSystemUser()->getId()
        );
    }

    public function testGetRandomHash(): void {
        $this->assertIsString(
            $this->userService->getRandomHash()
        );
    }

    public function testToUser(): void {
        $createTs      = new DateTimeImmutable();
        $email         = Uuid::uuid4() . '@keestash.com';
        $firstName     = Uuid::uuid4()->toString();
        $lastName      = Uuid::uuid4()->toString();
        $hash          = Uuid::uuid4()->toString();
        $password      = Uuid::uuid4()->toString();
        $phone         = '+004969123456';
        $website       = 'https://keestash.com';
        $languageCode  = 'de_DE';
        $locale        = 'de_DE';
        $roleNameOne   = Uuid::uuid4()->toString();
        $roleNameTwo   = Uuid::uuid4()->toString();
        $roleNameThree = Uuid::uuid4()->toString();

        $user = $this->userService->toUser(
            [
                'id'           => 9988
                , 'name'       => UserServiceTest::class
                , 'create_ts'  => [
                'date' => (new DateTimeImmutable())->format('Y-m-d H:i:s')
            ]
                , 'deleted'    => false
                , 'email'      => $email
                , 'first_name' => $firstName
                , 'last_name'  => $lastName
                , 'hash'       => $hash
                , 'locked'     => false
                , 'password'   => $password
                , 'phone'      => $phone
                , 'website'    => $website
                , 'language'   => $languageCode
                , 'locale'     => $locale
                , 'roles'      => [
                [
                    'id'     => 1
                    , 'name' => $roleNameOne
                ]
                , [
                    'id'     => 2
                    , 'name' => $roleNameTwo
                ]
                , [
                    'id'     => 3
                    , 'name' => $roleNameThree
                ]
            ]
            ]
        );
        $this->assertTrue($user->getId() === 9988);
        $this->assertTrue($user->getName() === UserServiceTest::class);
        $this->assertTrue($user->getCreateTs()->getTimestamp() === $createTs->getTimestamp());
        $this->assertTrue(false === $user->isDeleted());
        $this->assertTrue($user->getEmail() === $email);
        $this->assertTrue($user->getFirstName() === $firstName);
        $this->assertTrue($user->getLastName() === $lastName);
        $this->assertTrue($user->getHash() === $hash);
        $this->assertTrue(false === $user->isLocked());
        $this->assertTrue($user->getPassword() === $password);
        $this->assertTrue($user->getPhone() === $phone);
        $this->assertTrue($user->getWebsite() === $website);
        $this->assertTrue($user->getLanguage() === $languageCode);
        $this->assertTrue($user->getLocale() === $locale);
        $this->assertTrue(3 === $user->getRoles()->size());
        $this->assertTrue($user->getRoles()->get(1)->getName() === $roleNameOne);
        $this->assertTrue($user->getRoles()->get(2)->getName() === $roleNameTwo);
        $this->assertTrue($user->getRoles()->get(3)->getName() === $roleNameThree);
    }

    public function testToNewUser(): void {
        $email         = Uuid::uuid4() . '@keestash.com';
        $firstName     = Uuid::uuid4()->toString();
        $lastName      = Uuid::uuid4()->toString();
        $password      = Uuid::uuid4()->toString();
        $phone         = '+004969123456';
        $website       = 'https://keestash.com';
        $roleNameOne   = Uuid::uuid4()->toString();
        $roleNameTwo   = Uuid::uuid4()->toString();
        $roleNameThree = Uuid::uuid4()->toString();

        $user = $this->userService->toNewUser(
            [
                'user_name'    => UserServiceTest::class
                , 'email'      => $email
                , 'last_name'  => $lastName
                , 'first_name' => $firstName
                , 'password'   => $password
                , 'phone'      => $phone
                , 'website'    => $website
                , 'locked'     => false
                , 'deleted'    => false
                , 'roles'      => [
                [
                    'id'     => 1
                    , 'name' => $roleNameOne
                ]
                , [
                    'id'     => 2
                    , 'name' => $roleNameTwo
                ]
                , [
                    'id'     => 3
                    , 'name' => $roleNameThree
                ]
            ]
            ]
        );
        $this->assertTrue($user->getName() === UserServiceTest::class);
        $this->assertTrue($user->getEmail() === $email);
        $this->assertTrue($user->getLastName() === $lastName);
        $this->assertTrue($user->getFirstName() === $firstName);
        $this->assertTrue($user->getPhone() === $phone);
        $this->assertTrue($user->getWebsite() === $website);
        $this->assertTrue(false === $user->isLocked());
        $this->assertTrue(false === $user->isDeleted());
        $this->assertTrue(3 === $user->getRoles()->size());
        $this->assertTrue($user->getRoles()->get(1)->getName() === $roleNameOne);
        $this->assertTrue($user->getRoles()->get(2)->getName() === $roleNameTwo);
        $this->assertTrue($user->getRoles()->get(3)->getName() === $roleNameThree);
    }

    public function testIsDisabled(): void {
        $this->assertTrue(
            true === $this->userService->isDisabled(
                $this->userService->getSystemUser()
            )
        );
    }

    /**
     * @param string      $password
     * @param string      $passwordRepeat
     * @param string|null $exception
     * @return void
     * @dataProvider provideValidatePassword
     */
    public function testValidatePassword(string $password, string $passwordRepeat, ?string $exception): void {
        if (null !== $exception) {
            $this->expectException($exception);
        }
        $this->userService->validatePasswords($password, $passwordRepeat);
        $this->assertTrue(1 === 1);
    }

    public function testValidateNewUser(): void {
        $email         = Uuid::uuid4() . '@keestash.com';
        $firstName     = Uuid::uuid4()->toString();
        $lastName      = Uuid::uuid4()->toString();
        $password      = Uuid::uuid4()->toString();
        $phone         = '+004969123456';
        $website       = 'https://keestash.com';
        $roleNameOne   = Uuid::uuid4()->toString();
        $roleNameTwo   = Uuid::uuid4()->toString();
        $roleNameThree = Uuid::uuid4()->toString();

        $user   = $this->userService->toNewUser(
            [
                'user_name'    => UserServiceTest::class
                , 'email'      => $email
                , 'last_name'  => $lastName
                , 'first_name' => $firstName
                , 'password'   => $password
                , 'phone'      => $phone
                , 'website'    => $website
                , 'locked'     => false
                , 'deleted'    => false
                , 'roles'      => [
                [
                    'id'     => 1
                    , 'name' => $roleNameOne
                ]
                , [
                    'id'     => 2
                    , 'name' => $roleNameTwo
                ]
                , [
                    'id'     => 3
                    , 'name' => $roleNameThree
                ]
            ]
            ]
        );
        $result = $this->userService->validateNewUser($user);
        $this->assertTrue($result->length() > 0);
    }

    public function provideValidatePassword(): array {
        return [
            ['a', 'a', KeestashException::class]
            , ['b', 'a', KeestashException::class]
            , ['absdfsadfsad', 'absdfsadfsad', KeestashException::class]
            , ['asdadFSFS1213#@$@', 'asdadFSFS1213#@$@', null]
        ];
    }

    public function provideWebsite(): array {
        return [
            ['a', false]
            , ['ab', false]
            , ['ab@ab', false]
            , ['ab@ab.de', false]
            , ['keestash.com', false]
            , ['www.keestash.com', false]
            , ['https://www.keestash.com', true]
            , ['https://keestash.com', true]
        ];
    }

    public function provideEmailAddress(): array {
        return [
            ['a', false]
            , ['ab', false]
            , ['ab@ab', false]
            , ['ab@ab.de', true]
        ];
    }

    public function provideMinimumRequirementsPasswords(): array {
        return [
            ['a', false]
            , ['ab', false]
            , ['abc', false]
            , ['abcA', false]
            , ['abcAB', false]
            , ['abcABC', false]
            , ['abcABC1', false]
            , ['abcABC12', false]
            , ['abcABC123', false]
            , ['abcABC123}', true]
            , ['abcABC123}+', true]
            , ['abcABC123}+^', true]
        ];
    }

}
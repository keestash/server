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

namespace KST\Unit\Core\Service\Encryption\Password;

use Keestash\Core\DTO\Encryption\Password\Password;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Encryption\Password\IPassword;
use KSP\Core\Service\Encryption\Password\IPasswordService;
use KST\Unit\TestCase;

class PasswordServiceTest extends TestCase {

    private IPasswordService $passwordService;

    protected function setUp(): void {
        parent::setUp();
        $this->passwordService = $this->getService(IPasswordService::class);
    }

    /**
     * @param string $string
     * @param array  $characterSets
     * @return void
     * @dataProvider getFindCharacterSetDataProvider
     */
    public function testFindCharacterSet(string $string, array $characterSets): void {
        $result        = $this->passwordService->findCharacterSet($string);
        $haystack      = array_diff($result, $characterSets);
        $haystackCount = count($haystack);
        $this->assertTrue(0 === $haystackCount);
    }

    /**
     * @return void
     * @throws KeestashException
     * @dataProvider provideGeneratePasswordData
     */
    public function testGeneratePassword(
        int     $length
        , bool  $hasUpperCase
        , bool  $hasLowerCase
        , bool  $hasDigits
        , bool  $hasSpecialChars
        , int   $quality
        , array $characterSets
    ): void {
        $password = $this->passwordService->generatePassword($length, $hasUpperCase, $hasLowerCase, $hasDigits, $hasSpecialChars);
        $this->assertTrue($password->getQuality() === $quality);
        $this->assertTrue(strlen($password->getValue()) === $length);
        $difference = count(
            array_diff(
                $password->getCharacterSet()
                , $characterSets
            )
        );
        $this->assertTrue(0 === $difference);
    }

    /**
     * @param IPassword $password
     * @param int       $quality
     * @return void
     * @dataProvider provideTestMeasureQuality
     */
    public function testMeasureQuality(IPassword $password, int $quality): void {
        $result = $this->passwordService->measureQuality($password);
        $this->assertTrue($result->getQuality() === $quality);
    }

    public static function provideTestMeasureQuality(): array {
        $q1 = new Password();
        $q1->setValue('sdfasdfadfafdSDFSFSFFFAADFADSFSGSD1232342452424123123424@(#(#$(#$@#');
        $q2 = new Password();
        $q2->setValue('S@(#(#$(#$@#');
        return [
            [$q1, IPassword::QUALITY_BAD]
            , [$q2, IPassword::QUALITY_BAD]
        ];
    }

    public static function provideGeneratePasswordData(): array {
        return [
            [
                0
                , true // upper case
                , true // lower case
                , true // digits
                , true // special chars
                // results
                , IPassword::QUALITY_BAD
                , [
                    IPasswordService::SPECIAL_CHARACTERS
                    , IPasswordService::DIGITS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                    , IPasswordService::LOWER_CASE_CHARACTERS
                ]
            ]
            , [
                1
                , true // upper case
                , true // lower case
                , true // digits
                , false // special chars
                // results
                , IPassword::QUALITY_BAD
                , [
                    IPasswordService::DIGITS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                    , IPasswordService::LOWER_CASE_CHARACTERS
                ]
            ]
            , [
                5609124
                , true // upper case
                , true // lower case
                , true // digits
                , true // special chars
                // results
                , IPassword::QUALITY_GOOD
                , [
                    IPasswordService::DIGITS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                    , IPasswordService::LOWER_CASE_CHARACTERS
                    , IPasswordService::SPECIAL_CHARACTERS
                ]
            ]
        ];
    }

    public static function getFindCharacterSetDataProvider(): array {
        return [
            ['a', [IPasswordService::LOWER_CASE_CHARACTERS]]
            , [
                'aB', [
                    IPasswordService::LOWER_CASE_CHARACTERS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                ]
            ]
            , [
                'aB0', [
                    IPasswordService::LOWER_CASE_CHARACTERS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                    , IPasswordService::DIGITS
                ]
            ]
            , [
                'aB0@', [
                    IPasswordService::LOWER_CASE_CHARACTERS
                    , IPasswordService::UPPER_CASE_CHARACTERS
                    , IPasswordService::DIGITS
                    , IPasswordService::SPECIAL_CHARACTERS
                ]
            ]
        ];
    }

}
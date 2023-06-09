<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KST\Unit\Core\Service\Encryption\Mask;

use KSP\Core\Service\Encryption\IStringMaskService;
use KST\Unit\TestCase;

class StringMaskServiceTest extends TestCase {

    private IStringMaskService $stringMaskService;

    protected function setUp(): void {
        parent::setUp();
        $this->stringMaskService = $this->getService(IStringMaskService::class);
    }

    /**
     * @param string $plain
     * @param string $masked
     * @return void
     * @dataProvider provideStringsToMask
     */
    public function testMaskString(string $plain, string $masked): void {
        $this->assertTrue(
            $masked === $this->stringMaskService->mask($plain)
        );
    }

    public function provideStringsToMask(): array {
        return [
            ['a', 'x']
            , ['ab', 'xx']
            , ['abc', 'xxx']
            , ['abcd', 'xxxx']
            , ['abcde', 'abxde']
            , ['abcdef', 'abxxef']
            , ['abcdefg', 'abxxxfg']
            , ['abcdefgh', 'abxxxxgh']
            , ['abcdefghi', 'abxxxxxhi']
            , ['abcdefghij', 'abxxxxxxij']
            , ['abcdefghijk', 'abxxxxxxxjk']
            , ['abcdefghijkl', 'abxxxxxxxkl']
            , ['abcdefghijklm', 'abxxxxxxxlm']
            , ['abcdefghijklmn', 'abxxxxxxxmn']
            , ['abcdefghijklmno', 'abxxxxxxxno']
            , ['abcdefghijklmno', 'abxxxxxxxno']
            , ['abcdefghijklmnop', 'abxxxxxxxop']
            , ['abcdefghijklmnopq', 'abxxxxxxxpq']
            , ['abcdefghijklmnopqr', 'abxxxxxxxqr']
            , ['abcdefghijklmnopqrs', 'abxxxxxxxrs']
            , ['abcdefghijklmnopqrst', 'abxxxxxxxst']
            , ['abcdefghijklmnopqrstu', 'abxxxxxxxtu']
            , ['abcdefghijklmnopqrstuv', 'abxxxxxxxuv']
            , ['abcdefghijklmnopqrstuvw', 'abxxxxxxxvw']
            , ['abcdefghijklmnopqrstuvwx', 'abxxxxxxxwx']
            , ['abcdefghijklmnopqrstuvwxy', 'abxxxxxxxxy']
            , ['abcdefghijklmnopqrstuvwxyz', 'abxxxxxxxyz']
        ];
    }

}
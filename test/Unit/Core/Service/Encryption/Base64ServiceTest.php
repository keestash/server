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

namespace KST\Unit\Core\Service\Encryption;

use Keestash\Core\Service\Encryption\Base64Service;
use KSP\Core\Service\Encryption\IBase64Service;
use KST\TestCase;

class Base64ServiceTest extends TestCase {

    private Base64Service $base64Service;

    protected function setUp(): void {
        parent::setUp();
        $this->base64Service = $this->getService(IBase64Service::class);
    }

    public function testEncryptValue(): void {
        $this->assertTrue(
            "a2Vlc3Rhc2h1bml0dGVzdHM=" === $this->base64Service->encrypt('keestashunittests')
        );
    }

    public function testEncryptArrayRecursive(): void {
        $array  = [
            'key1'   => '727379030cbd07a656f339c8713debe5',
            'array1' => [
                'key2' => 'f12390d5c4d4ca6d4e4ae7953e794008'
            ],
            'array2' => [
                'key3'   => 'a7abd3c435404ebe4abf054dd25c7017',
                'array3' => [
                    'key4' => 'ece65c1f1c798c2b5d10f77bacf7bbec',
                    'key5' => '8f76651dc535c3c50dbdc7fdcbdfcae9'
                ]
            ],
            'array4' => [
                'key6'   => '688e1237645afe5f85a679bf13adf48f',
                'array5' => [
                    'key7'   => '8a23cc91360672a0809dfe11fb13322e',
                    'key8'   => '4ce966954fd3732d3aa9f49a5b413a52',
                    'array6' => [
                        'key9' => 'c4cd46ebb82ff262ef6ee8d5b09720b3'
                    ]
                ]
            ]
        ];
        $result = [
            'key1'   => 'NzI3Mzc5MDMwY2JkMDdhNjU2ZjMzOWM4NzEzZGViZTU=',
            'array1' => [
                'key2' => 'ZjEyMzkwZDVjNGQ0Y2E2ZDRlNGFlNzk1M2U3OTQwMDg='
            ],
            'array2' => [
                'key3'   => 'YTdhYmQzYzQzNTQwNGViZTRhYmYwNTRkZDI1YzcwMTc=',
                'array3' => [
                    'key4' => 'ZWNlNjVjMWYxYzc5OGMyYjVkMTBmNzdiYWNmN2JiZWM=',
                    'key5' => 'OGY3NjY1MWRjNTM1YzNjNTBkYmRjN2ZkY2JkZmNhZTk='
                ]
            ],
            'array4' => [
                'key6'   => 'Njg4ZTEyMzc2NDVhZmU1Zjg1YTY3OWJmMTNhZGY0OGY=',
                'array5' => [
                    'key7'   => 'OGEyM2NjOTEzNjA2NzJhMDgwOWRmZTExZmIxMzMyMmU=',
                    'key8'   => 'NGNlOTY2OTU0ZmQzNzMyZDNhYTlmNDlhNWI0MTNhNTI=',
                    'array6' => [
                        'key9' => 'YzRjZDQ2ZWJiODJmZjI2MmVmNmVlOGQ1YjA5NzIwYjM='
                    ]
                ]
            ]
        ];
        $this->assertEquals(
            $result,
            $this->base64Service->encryptArrayRecursive($array)
        );
    }

}


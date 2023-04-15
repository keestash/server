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

namespace KST\Integration\Core\Service\CSV;

use Keestash\Exception\File\FileNotFoundException;
use KSP\Core\Service\CSV\ICSVService;
use KST\Integration\TestCase;

class CSVServiceTest extends TestCase {

    public const EXAMPLE_CSV_FOR_TEST_FROM_STRING = 'Username;Identifier;First name;Last name
booker12;9012;Rachel;Booker
grey07;2070;Laura;Grey
johnson81;4081;Craig;Johnson
jenkins46;9346;Mary;Jenkins
smith79;5079;Jamie;Smith';

    public function testWithFileNotFound(): void {
        $this->expectException(FileNotFoundException::class);
        /** @var ICSVService $csvService */
        $csvService = $this->getService(ICSVService::class);
        $csvService->readFile(sys_get_temp_dir());
    }

    public function testReadFile(): void {
        /** @var ICSVService $csvService */
        $csvService = $this->getService(ICSVService::class);
        $users      = $csvService->readFile(
            __DIR__ . '/example.csv'
            , true
            , ';'
        );

        $this->assertCount(5, $users);

        foreach ($users as $user) {
            $this->assertArrayHasKey('Username', $user);
            $this->assertArrayHasKey('Identifier', $user);
            $this->assertArrayHasKey('First name', $user);
            $this->assertArrayHasKey('Last name', $user);
        }
    }

    public function testReadString(): void {
        $this->markTestSkipped('not working yet, fix it');
        /** @var ICSVService $csvService */
        $csvService = $this->getService(ICSVService::class);
        $users      = $csvService->readString(
            CSVServiceTest::EXAMPLE_CSV_FOR_TEST_FROM_STRING
            , true
            , ';'
        );

        $this->assertCount(5, $users);

        foreach ($users as $user) {
            $this->assertArrayHasKey('Username', $user);
            $this->assertArrayHasKey('Identifier', $user);
            $this->assertArrayHasKey('First name', $user);
            $this->assertArrayHasKey('Last name', $user);
        }
    }

}
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

namespace KST\Unit\Core\Service\Config;

use KSP\Core\Service\Config\IIniConfigService;
use KST\TestCase;

class IniConfigServiceTest extends TestCase {

    private IIniConfigService $configService;

    protected function setUp(): void {
        parent::setUp();
        $this->configService = $this->getService(IIniConfigService::class);
    }

    /**
     * @param string $key
     * @param        $value
     * @param        $default
     * @return void
     * @dataProvider provideData
     */
    public function testGetValue(string $key, $value, $default): void {
        $this->assertTrue($value === $this->configService->getValue($key, $default));
    }

    public function testGetAll(): void {
        $all = $this->configService->getAll();
        $this->assertIsArray($all);
        $this->assertCount(1, $all);
        $this->assertArrayHasKey('ini.test.config', $all);
    }

    /**
     * @return void
     * @dataProvider provideToBytesData
     */
    public function testToBytes(string $iniValue, int $intValue): void {
        $this->assertTrue($intValue === $this->configService->toBytes($iniValue));
    }

    public function provideToBytesData(): array {
        return [
            ['3M', 3145728]
            , ['24M', 25165824]
            , ['1G', 1073741824]
        ];
    }

    public function provideData(): array {
        return [
            ['ini.test.config', true, null]
            , ['test.config.nonExisting', null, null]
        ];
    }

}
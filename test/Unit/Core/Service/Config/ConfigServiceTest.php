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

use KSP\Core\Service\Config\IConfigService;
use KST\Unit\TestCase;

class ConfigServiceTest extends TestCase {

    private IConfigService $configService;

    protected function setUp(): void {
        parent::setUp();
        $this->configService = $this->getService(IConfigService::class);
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
        $this->assertArrayHasKey('test.config', $all);
    }

    public function provideData(): array {
        return [
            ['test.config', true, null]
            , ['test.config.nonExisting', null, null]
        ];
    }

}
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

namespace KST\Unit\Core\Service\HTTP\Output;

use KSP\Core\Service\HTTP\Output\ISanitizerService;
use KST\TestCase;

class SanitizerServiceTest extends TestCase {

    private ISanitizerService $sanitizerService;

    protected function setUp(): void {
        parent::setUp();
        $this->sanitizerService = $this->getService(ISanitizerService::class);
    }

    public function testSanitize(): void {
        $this->markTestSkipped('implement once sanitizer is implemented');
    }

    public function testSanitizeAll(): void {
        $this->markTestSkipped('implement once sanitizer is implemented');
    }

}
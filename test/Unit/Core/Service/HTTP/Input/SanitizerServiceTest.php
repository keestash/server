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

namespace KST\Unit\Core\Service\HTTP\Input;

use KSP\Core\Service\HTTP\Input\ISanitizerService;
use KST\Unit\TestCase;

class SanitizerServiceTest extends TestCase {

    private ISanitizerService $sanitizerService;

    #[\Override]
    protected function setUp(): void {
        parent::setUp();
        $this->sanitizerService = $this->getService(ISanitizerService::class);
    }

    /**
     * @param string $dirty
     * @param string $clean
     * @return void
     * @dataProvider provideSanitize
     */
    public function testSanitize(string $dirty, string $clean): void {
        $this->assertTrue(
            $clean === $this->sanitizerService->sanitize($dirty)
        );
    }

    /**
     * @return void
     * @dataProvider provideSanitizeAll
     */
    public function testSanitizeAll(array $input): void {
        $result = [
            'alert' => 'tests'
            , 'img' => ''
        ];
        $output = $this->sanitizerService->sanitizeAll($input);

        $this->assertTrue(
            $output['alert'] === $result['alert']
        );
        $this->assertTrue(
            $output['img'] === $result['img']
        );
    }

    public static function provideSanitize(): array {
        return [
            ['<alert>tests</alert>', 'tests']
            , ['<img src="javascript:evil();" onload="evil();" />', '']
        ];
    }

    public static function provideSanitizeAll(): array {
        return [
            [
                [
                    'alert' => '<alert>tests</alert>',
                    'img'   => '<img src="javascript:evil();" onload="evil();" />'
                ],
            ],
        ];
    }

}
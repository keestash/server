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

namespace KSA\PasswordManager\Test\Unit;

class TestCase extends \KST\Unit\TestCase {

    #[\Override]
    public function testByPassMeaninglessRestrictions(): void {
        // https://github.com/sebastianbergmann/phpunit/issues/5132
        $this->assertIsString('Because PHPUnit thinks it must dictate how I organize my tests, I had to switch from abstract to regular class and this test');
    }

}
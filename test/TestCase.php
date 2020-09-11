<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KST;

use Keestash;
use Keestash\Server;
use KST\Api\SimpleApiRunner;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase {

    protected function setUp(): void {
        parent::setUp();

        $keestash = __DIR__ . "/../lib/Keestash.php";
        $keestash = realpath($keestash);
        /** @noinspection PhpIncludeInspection */
        require_once $keestash;

        Keestash::init();
    }

    protected function getServer(): Server {
        return Keestash::getServer();
    }

    protected function getSimpleApiRunner(): SimpleApiRunner {
        return new SimpleApiRunner();
    }


}

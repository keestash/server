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

use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase as FrameworkTestCase;

class TestCase extends FrameworkTestCase {


    protected function getMockedBackend(): MockObject {
        $backend = $this->getMockBuilder(\Keestash\Core\Backend\MySQLBackend::class)
            ->disableOriginalConstructor()
            ->onlyMethods(
                [
                    'connect'
                    , 'disconnect'
                    , 'isConnected'
                    , 'getSchemaName'
                    , 'getConnection'
                    , 'getTables'
                ]
            )
            ->getMock();

        $backend->method('connect')
            ->willReturn(true);
        $backend->method('disconnect')
            ->willReturn(true);
        $backend->method('isConnected')
            ->willReturn(true);
        $backend->method('getSchemaName')
            ->willReturn('unittest');
        $backend->method('getTables')
            ->willReturn([]);

        return $backend;
    }

}

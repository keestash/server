<?php
declare(strict_types=1);
/**
 * server
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KST\Core\Backend;

use Doctrine\DBAL\Connection;
use Keestash\Core\Backend\MySQLBackend;
use KST\TestCase;
use PDO;

class MySQLBackendTest extends TestCase {

    /** @var MySQLBackend */
    private $mySqlBackend;

    public function testAttributes() {
        $this->assertTrue($this->mySqlBackend->connect());
        $this->assertTrue($this->mySqlBackend->disconnect());
        $this->assertTrue($this->mySqlBackend->isConnected());
        $this->assertInstanceOf(
            PDO::class, $this->mySqlBackend->getConnection()
        );
        $this->assertInstanceOf(
            Connection::class, $this->mySqlBackend->getDoctrineConnection()
        );
    }

    protected function setUp(): void {
        parent::setUp();

        $mySqlBackendMock = $this->getMockBuilder(MySQLBackendTest::class)
            ->setConstructorArgs(
                [
                    "schemaName" => "myTestDb"
                ]
            )
            ->addMethods(
                [
                    "connect"
                    , "disconnect"
                    , "getConnection"
                    , "isConnected"
                    , "getDoctrineConnection"
                ]
            )
            ->getMock();

        $mySqlBackendMock->method('connect')->willReturn(true);
        $mySqlBackendMock->method('disconnect')->willReturn(true);
        $mySqlBackendMock->method('isConnected')->willReturn(true);

        $mySqlBackendMock->method('getConnection')->willReturn(
            $this->getMockBuilder(PDO::class)
                ->disableOriginalConstructor()
                ->getMock()
        );
        $mySqlBackendMock->method('getDoctrineConnection')->willReturn(
            $this->getMockBuilder(Connection::class)
                ->disableOriginalConstructor()
                ->getMock()
        );

        $this->mySqlBackend = $mySqlBackendMock;
    }

}

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

namespace KST\Core\Repository\ApiLog;

use Keestash\Core\DTO\Instance\Request\APIRequest;
use Keestash\Core\DTO\Token\Token;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Repository\ApiLog\ApiLogRepository;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KST\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ApiLogRepositoryTest extends TestCase {

    /** @var MockObject|IApiLogRepository $logRepository */
    private $logRepository;

    public function testLog(): void {
        $request = new APIRequest();
        $request->setRoute("my/awesome/route");
        $request->setStart(time() - 3600);
        $request->setEnd(time());
        $request->setToken(new Token());

        $this->logRepository->method("log")
            ->with($request)
            ->willReturn(1);

        $id = $this->logRepository->log($request);
        $this->assertEquals(1, $id);
    }

    public function testRemoveForUser(): void {
        $user = new User();
        $this->logRepository->method("removeForUser")
            ->with($user)
            ->willReturn(true);

        $this->assertTrue(
            $this->logRepository->removeForUser($user)
        );

    }

    protected function setUp(): void {
        parent::setUp();

        $this->logRepository = $this->getMockBuilder(ApiLogRepository::class)
            ->onlyMethods(
                [
                    "log"
                    , "removeForUser"
                ]
            )
            ->disableOriginalConstructor()
            ->getMock();
    }

}

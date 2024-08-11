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

namespace KST\Integration\Core\Repository\ApiLog;

use DateTime;
use Keestash\Core\DTO\Instance\Request\ApiLog;
use Keestash\Core\DTO\Token\Token;
use KSP\Core\DTO\Instance\Request\ApiLogInterface;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\User\IUserRepository;
use KST\Integration\TestCase;
use KST\Service\Service\UserService;
use Ramsey\Uuid\Uuid;

class ApiLogRepositoryTest extends TestCase {

    public function testLog(): void {
        $serviceManager = $this->getServiceManager();
        /** @var IUserRepository $userRepository */
        $userRepository = $serviceManager->get(IUserRepository::class);

        $token = new Token();
        $token->setId(1);
        $token->setCreateTs(new DateTime());
        $token->setValue(ApiLogRepositoryTest::class);
        $token->setName(ApiLogRepositoryTest::class);
        $token->setUser(
            $userRepository->getUserById((string) UserService::TEST_USER_ID_2)
        );

        /** @var IApiLogRepository $apiLogRepository */
        $apiLogRepository = $this->getServiceManager()->get(IApiLogRepository::class);
        $request          = new ApiLog(
            Uuid::uuid4()->toString(),
            Uuid::uuid4()->toString(),
            '',
            new \DateTimeImmutable(),
            (new \DateTimeImmutable())->modify('+3 minutes'),
            new \DateTimeImmutable(),
        );

        $request = $apiLogRepository->log($request);
        $this->assertInstanceOf(ApiLogInterface::class, $request); // no exception is thrown
    }

//    public function testRemoveForUser(): void {
//        $apiLogRepository = $this->getServiceManager()->get(IApiLogRepository::class);
//        $user             = new User();
//
//        $this->assertTrue(
//            $apiLogRepository->removeForUser($user)
//        );
//
//    }

}

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

namespace KSA\Settings\Test\Integration\Api\User;

use KSA\Settings\Api\User\UserLock;
use KSA\Settings\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserStateService;
use Ramsey\Uuid\Uuid;

class UserLockTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var UserLock $userLock */
        $userLock = $this->getService(UserLock::class);

        $response = $userLock->handle(
            $this->getVirtualRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::BAD_REQUEST === $response->getStatusCode());
    }

    public function testWithMissingPermission(): void {
        /** @var UserLock $userLock */
        $userLock = $this->getService(UserLock::class);

        $response = $userLock->handle(
            $this->getVirtualRequest(
                [
                    'user_id' => 5
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testRegularCase(): void {
        /** @var IUserStateService $userStateService */
        $userStateService = $this->getService(IUserStateService::class);
        /** @var UserLock $userLock */
        $userLock = $this->getService(UserLock::class);
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );

        $response = $userLock->handle(
            $this->getVirtualRequest(
                [
                    'user_id' => $user->getId()
                ]
                , $user
            )
        );

        $this->assertStatusCode(IResponse::OK, $response);
        $userStateService->clear($user);
        $this->removeUser($user);
    }

}

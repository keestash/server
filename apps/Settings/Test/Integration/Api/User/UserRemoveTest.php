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

use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use KSA\Settings\ConfigProvider;
use KSA\Settings\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\RBAC\IRole;
use Ramsey\Uuid\Uuid;

class UserRemoveTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var RBACRepositoryInterface $rbacRepository */
        $rbacRepository = $this->getService(RBACRepositoryInterface::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );


        $rbacRepository->assignRoleToUser(
            $user
            , $rbacRepository->getRole(IRole::ROLE_USER_ADMIN)
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_REMOVE
                    , []
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);
        $this->removeUser($user);
    }

    public function testWithMissingPermission(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_REMOVE
                    , [
                        'user_id' => $user->getId()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::FORBIDDEN, $response);
        $this->removeUser($user);
    }

    public function testRegularCase(): void {
        /** @var RBACRepositoryInterface $rbacRepository */
        $rbacRepository = $this->getService(RBACRepositoryInterface::class);
        $password       = Uuid::uuid4()->toString();
        $user           = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $rbacRepository->assignRoleToUser(
            $user
            , $rbacRepository->getRole(IRole::ROLE_USER_ADMIN)
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::USER_REMOVE
                    , [
                        'user_id' => $user->getId()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->removeUser($user);
    }

}
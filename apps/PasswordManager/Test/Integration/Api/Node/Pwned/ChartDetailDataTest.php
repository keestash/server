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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Pwned;

use DateTimeImmutable;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\Node\Pwned\Passwords;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use KSP\Core\DTO\RBAC\IRole;
use Ramsey\Uuid\Uuid;

class ChartDetailDataTest extends TestCase {

    public function testWithEmptyResponse(): void {
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
                    IVerb::GET
                    , ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL
                    , []
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);
        $body = json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertTrue(2 === count($body));
        $this->assertArrayHasKey('passwords', $body);
        $this->assertArrayHasKey('breaches', $body);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testWithSingleResponse(): void {
        /** @var PwnedPasswordsRepository $pwnPasswordRepository */
        $pwnPasswordRepository = $this->getService(PwnedPasswordsRepository::class);
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

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $severity = 7;

        $passwords = $pwnPasswordRepository->replace(
            new Passwords(
                $edge->getNode()
                , $severity
                , new DateTimeImmutable()
                , new DateTimeImmutable()
            )
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::GET
                    , ConfigProvider::PASSWORD_MANAGER_NODE_PWNED_CHART_DETAIL
                    , []
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

}
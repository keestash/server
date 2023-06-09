<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Folder;

use KSA\PasswordManager\Api\Node\Folder\Create;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

class CreateTest extends TestCase {

    public function testWithOutParameter(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithSingleParameterName(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(['name' => 'test'])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithSingleParameterParent(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(['parent' => 999])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithCredentialAsParent(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'parent' => "2"
                    , 'name' => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNoAccess(): void {
        $firstUser          = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $secondUserPassword = Uuid::uuid4()->toString();
        $secondUser         = $this->createUser(
            Uuid::uuid4()->toString()
            , $secondUserPassword
        );

        $edge = $this->createAndInsertFolder(
            $firstUser
            , Uuid::uuid4()->toString()
            , $this->getRootFolder($firstUser)
        );

        $headers  = $this->login($secondUser, $secondUserPassword);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_NODE_CREATE
                    , [
                        'node_id' => $edge->getNode()->getId()
                        , 'name'  => Uuid::uuid4()->toString()
                    ]
                    , $secondUser
                    , $headers
                )
            );
        $this->assertStatusCode(IResponse::UNAUTHORIZED, $response);
        $this->logout($headers, $secondUser);
    }

    public function testAddToRoot(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getVirtualRequest(
                [
                    'node_id' => 'root'
                    , 'name'  => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testAddToFolder(): void {

        /** @var Create $create */
        $create = $this->getServiceManager()->get(Create::class);

        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $edge = $this->createAndInsertFolder(
            $user
            , Uuid::uuid4()->toString()
            , $this->getRootFolder($user)
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_NODE_CREATE
                    , [
                        'node_id' => $edge->getNode()->getId()
                        , 'name'  => Uuid::uuid4()->toString()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
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

use DateTime;
use KSA\PasswordManager\Api\Node\Folder\Create;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Node;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KST\TestCase;

class CreateTest extends TestCase {

    public function testWithOutParameter(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest()
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithSingleParameterName(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(['name' => 'test'])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithSingleParameterParent(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(['parent' => 999])
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithCredentialAsParent(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent' => "2"
                    , 'name' => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testWithNoAccess(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent' => "22"
                    , 'name' => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testAddToRoot(): void {
        /** @var Create $create */
        $create   = $this->getServiceManager()->get(Create::class);
        $response = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent' => 'root'
                    , 'name' => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testAddToFolder(): void {

        /** @var Create $create */
        $create = $this->getServiceManager()->get(Create::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getServiceManager()->get(NodeRepository::class);

        $folder = new Folder();
        $folder->setUser($this->getUser());
        $folder->setCreateTs(new DateTime());
        $folder->setUpdateTs(new DateTime());
        $folder->setName('TestAddToFolder');
        $folder->setType(Node::FOLDER);
        $id = $nodeRepository->addFolder($folder);
        $folder->setId((int) $id);

        $response = $create->handle(
            $this->getDefaultRequest(
                [
                    'parent' => (string) $folder->getId()
                    , 'name' => 'CreateTest'
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

}
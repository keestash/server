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

namespace KSA\PasswordManager\Test\Unit\Service\Node\Edge;

use DateTime;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\Edge\EdgeService;
use KST\TestCase;

class EdgeServiceTest extends TestCase {

    private EdgeService       $edgeService;
    private CredentialService $credentialService;

    protected function setUp(): void {
        parent::setUp();
        $this->edgeService       = $this->getServiceManager()->get(EdgeService::class);
        $this->credentialService = $this->getServiceManager()->get(CredentialService::class);
    }

    public function testPrepareRegularEdge(): void {
        $user       = $this->getUser();
        $folder     = new Folder();
        $credential = new Credential();
        $credential->setUser($user);
        $edge = $this->edgeService->prepareRegularEdge($credential, $folder);

        $this->assertTrue($edge instanceof Edge);
        $this->assertTrue($edge->getNode() === $credential);
        $this->assertTrue($edge->getParent() === $folder);
        $this->assertTrue(null === $edge->getExpireTs());
        $this->assertTrue($edge->getOwner() === $user);
        $this->assertTrue($edge->getCreateTs() < new DateTime());
    }

}
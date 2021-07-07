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

namespace KSA\PasswordManager\Test\Unit\Service\Node;

use DateTime;
use DateTimeInterface;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node;
use KSA\PasswordManager\Entity\Password\Credential;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\NodeService;
use KST\TestCase;

class NodeServiceTest extends TestCase {

    private NodeService       $nodeService;
    private NodeRepository    $nodeRepository;
    private CredentialService $credentialService;

    protected function setUp(): void {
        parent::setUp();
        $this->nodeService       = $this->getServiceManager()
            ->get(NodeService::class);
        $this->nodeRepository    = $this->getServiceManager()
            ->get(NodeRepository::class);
        $this->credentialService = $this->getServiceManager()
            ->get(CredentialService::class);
    }

    /**
     * TODO test shared edge
     */
    public function testIsShareable(): void {
        $credential = $this->provideCredential();
        $this->nodeRepository->addCredential($credential);
        $shareable = $this->nodeService->isShareable($credential->getId(), (string) $this->getUser()->getId());
        $this->assertTrue(false === $shareable);
    }

    private function provideCredential(): Credential {
        return $this->credentialService->createCredential(
            "topsecret"
            , "myawsome.route"
            , "keestash.com"
            , "keestash"
            , $this->getUser()
            , new Folder()
        );
    }

    public function testPrepareSharedEdge(): void {
        $credential = $this->provideCredential();
        $this->nodeRepository->addCredential($credential);
        $edge               = $this->nodeService->prepareSharedEdge($credential->getId(), (string) $this->getUser()->getId());
        $expectedExpireDate = new DateTime();
        $expectedExpireDate->modify('+10 days');

        $this->assertTrue($edge instanceof Edge);
        $this->assertTrue($edge->getExpireTs() instanceof DateTimeInterface);
        $this->assertTrue($edge->getExpireTs()->format("Y.m.d") === $expectedExpireDate->format("Y.m.d"));
        $this->assertTrue($edge->getType() === Edge::TYPE_SHARE);
        $this->assertTrue($edge->getCreateTs() < new DateTime());
    }

    public function testPrepareRegularEdge(): void {
        $user       = $this->getUser();
        $folder     = new Folder();
        $credential = new Credential();
        $edge       = $this->nodeService->prepareRegularEdge($credential, $folder, $user);
        $this->assertTrue($edge instanceof Edge);
        $this->assertTrue($edge->getNode() === $credential);
        $this->assertTrue($edge->getParent() === $folder);
        $this->assertTrue(null === $edge->getExpireTs());
        $this->assertTrue($edge->getOwner() === $user);
        $this->assertTrue($edge->getCreateTs() < new DateTime());
    }

    public function testCreateRootFolder(): void {
        $user     = $this->getUser();
        $folderId = 1;
        $root     = $this->nodeService->createRootFolder(
            $folderId
            , $user
        );

        $this->assertTrue($folderId === $root->getId());
        $this->assertTrue($root->getName() === Node::ROOT);
        $this->assertTrue($root->getUser()->getId() === $user->getId());
        $this->assertTrue($root->getCreateTs() < new DateTime());
    }

    public function testValidType(): void {
        $this->assertTrue(false === $this->nodeService->validType('thisisnotatype'));
        $this->assertTrue(true === $this->nodeService->validType('root'));
        $this->assertTrue(true === $this->nodeService->validType('credential'));
        $this->assertTrue(true === $this->nodeService->validType('folder'));
    }

    public function testIsDeletable(): void {
        $this->assertTrue(false === $this->nodeService->isDeletable('thisisnotatype'));
        $this->assertTrue(false === $this->nodeService->isDeletable('root'));
        $this->assertTrue(true === $this->nodeService->validType('credential'));
        $this->assertTrue(true === $this->nodeService->validType('folder'));
    }

}
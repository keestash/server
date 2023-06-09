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

namespace KSA\PasswordManager\Test\Integration\Api\Node\Organization;

use DateTimeImmutable;
use Keestash\Core\DTO\Organization\Organization;
use KSA\PasswordManager\Api\Node\Organization\Update;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository as OrganizationNodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\Settings\Service\IOrganizationService;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class UpdateTest extends TestCase {

    private LoggerInterface            $logger;
    private IUserService               $userService;
    private IOrganizationService       $organizationService;
    private OrganizationNodeRepository $organizationNodeRepository;
    private NodeRepository             $nodeRepository;
    private CredentialService          $credentialService;

    protected function setUp(): void {
        parent::setUp();
        $this->logger                     = $this->getService(LoggerInterface::class);
        $this->userService                = $this->getService(IUserService::class);
        $this->organizationService        = $this->getService(IOrganizationService::class);
        $this->organizationNodeRepository = $this->getService(OrganizationNodeRepository::class);
        $this->nodeRepository             = $this->getService(NodeRepository::class);
        $this->credentialService          = $this->getService(CredentialService::class);
    }

    public function testWithEmptyRequest(): void {
        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getVirtualRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }

    public function testWithOrganizationNotFund(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => 9999999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
        $this->removeUser($user);
    }

    public function testWithInactiveOrganization(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(null);
        $organization->setName(AddTest::class);
        $organization->setPassword(
            $this->userService->hashPassword(md5((string) time()))
        );
        $organization = $this->organizationService->add($organization);
        $this->organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);

        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $organization->getId()
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
        $this->removeUser($user);
    }

    public function testRegular(): void {
        $this->markTestSkipped('the whole organization thing is broken, fix it');
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );

        $organization = $this->createAndInsertOrganization(Uuid::uuid4()->toString());
        $this->organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);
        $newOrganization = $this->createAndInsertOrganization(Uuid::uuid4()->toString());

        /** @var Update $add */
        $add          = $this->getService(Update::class);
        $response     = $add->handle(
            $this->getVirtualRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $newOrganization->getId()
                ]
            )
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($responseBody['organization']['name'] === $newOrganization->getName());
        $this->assertTrue($responseBody['type'] === Edge::TYPE_ORGANIZATION);
    }

}
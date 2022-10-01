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
use KSA\PasswordManager\Test\TestCase;
use KSA\Settings\Repository\OrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;

class UpdateTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }

    public function testWithOrganizationNotFund(): void {
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        $credential        = $credentialService->createCredential(
            md5((string) time())
            , 'https://keestash.com'
            , 'keestash'
            , UpdateTest::class
            , $this->getUser()
        );

        $edge = $credentialService->insertCredential(
            $credential
            , $nodeRepository->getRootForUser(
            $this->getUser()
            , 0
            , 0
        )
        );

        /** @var Update $update */
        $update   = $this->getService(Update::class);
        $response = $update->handle(
            $this->getDefaultRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => 9999999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testWithInactiveOrganization(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(OrganizationRepository::class);
        /** @var OrganizationNodeRepository $organizationNodeRepository */
        $organizationNodeRepository = $this->getService(OrganizationNodeRepository::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        $credential        = $credentialService->createCredential(
            md5((string) time())
            , 'https://keestash.com'
            , 'keestash'
            , AddTest::class
            , $this->getUser()
        );

        $edge = $credentialService->insertCredential(
            $credential
            , $nodeRepository->getRootForUser(
            $this->getUser()
            , 0
            , 0
        )
        );

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(null);
        $organization->setName(AddTest::class);
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $organization = $organizationRepository->insert($organization);
        $organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);

        /** @var Update $add */
        $add      = $this->getService(Update::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $organization->getId()
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::FORBIDDEN === $response->getStatusCode());
    }

    public function testRegular(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(OrganizationRepository::class);
        /** @var OrganizationNodeRepository $organizationNodeRepository */
        $organizationNodeRepository = $this->getService(OrganizationNodeRepository::class);
        /** @var NodeRepository $nodeRepository */
        $nodeRepository = $this->getService(NodeRepository::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        $credential        = $credentialService->createCredential(
            md5((string) time())
            , 'https://keestash.com'
            , 'keestash'
            , AddTest::class
            , $this->getUser()
        );

        $edge = $credentialService->insertCredential(
            $credential
            , $nodeRepository->getRootForUser(
            $this->getUser()
            , 0
            , 0
        )
        );

        $organization = new Organization();
        $organization->setCreateTs(new DateTimeImmutable());
        $organization->setActiveTs(new DateTimeImmutable());
        $organization->setName(AddTest::class);
        $organization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $organization = $organizationRepository->insert($organization);
        $organizationNodeRepository->addNodeToOrganization($edge->getNode(), $organization);

        $name            = AddTest::class . 'new';
        $newOrganization = new Organization();
        $newOrganization->setCreateTs(new DateTimeImmutable());
        $newOrganization->setActiveTs(new DateTimeImmutable());
        $newOrganization->setName($name);
        $newOrganization->setPassword(
            $userService->hashPassword(md5((string) time()))
        );
        $newOrganization = $organizationRepository->insert($newOrganization);

        /** @var Update $add */
        $add          = $this->getService(Update::class);
        $response     = $add->handle(
            $this->getDefaultRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $newOrganization->getId()
                ]
            )
        );
        $responseBody = $this->getResponseBody($response);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $this->assertTrue($responseBody['organization']['name'] === $name);
        $this->assertTrue($responseBody['type'] === Edge::TYPE_ORGANIZATION);
    }

}
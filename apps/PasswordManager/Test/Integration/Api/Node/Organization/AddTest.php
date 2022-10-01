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
use KSA\PasswordManager\Api\Node\Organization\Add;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\OrganizationRepository as OrganizationNodeRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Test\TestCase;
use KSA\Settings\Repository\OrganizationRepository;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;

class AddTest extends TestCase {

    public function testWithEmptyRequest(): void {
        /** @var Add $add */
        $add      = $this->getService(Add::class);
        $response = $add->handle(
            $this->getDefaultRequest()
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_ACCEPTABLE === $response->getStatusCode());
    }

    public function testWithNodeAlreadyAssigned(): void {
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

        /** @var Add $add */
        $add      = $this->getService(Add::class);
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

    public function testWithOrganizationNotFund(): void {
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

        /** @var Add $add */
        $add      = $this->getService(Add::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => 9999999
                ]
            )
        );
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::NOT_FOUND === $response->getStatusCode());
    }

    public function testWithDeactivatedOrganization(): void {
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

        /** @var Add $add */
        $add      = $this->getService(Add::class);
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
        $organizationRepository->remove($organization);
    }

    public function testRegularCase(): void {
        /** @var IUserService $userService */
        $userService = $this->getService(IUserService::class);
        /** @var OrganizationRepository $organizationRepository */
        $organizationRepository = $this->getService(OrganizationRepository::class);
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

        /** @var Add $add */
        $add      = $this->getService(Add::class);
        $response = $add->handle(
            $this->getDefaultRequest(
                [
                    'node_id'           => $edge->getNode()->getId()
                    , 'organization_id' => $organization->getId()
                ]
            )
        );
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
        $this->assertTrue(IResponse::OK === $response->getStatusCode());
        $organizationRepository->remove($organization);
    }

}
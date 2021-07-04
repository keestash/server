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

namespace KSA\PasswordManager\Test\Integration\Api\Share;

use KSA\PasswordManager\Api\Share\PublicShareSingle;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KST\TestCase;

class PublicShareSingleTest extends TestCase {

    public function testPublicShareSingle(): void {
        $user = $this->getUser();
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getServiceManager()->get(PublicShareRepository::class);

        $parent = new Folder();
        $node   = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $user
            , $parent
        );
        $edge   = $credentialService->insertCredential($node, $parent);
        $node   = $edge->getNode();

        $publicShare = $shareService->createPublicShare($node);
        $publicShare->setNodeId($node->getId());
        $node->setPublicShare($publicShare);
        $node = $shareRepository->shareNode($node);

        $request  = $this->getRequestService()
            ->getRequestWithToken($user);
        $response = $publicShareSingle->handle($request->withAttribute("hash", $publicShare->getHash()));

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testPublicShareWithoutHash(): void {
        $user = $this->getUser();
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        $request           = $this->getRequestService()
            ->getRequestWithToken($user);

        $response = $publicShareSingle->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testExpiredPublicShare(): void {
        $user = $this->getUser();
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getServiceManager()->get(PublicShareRepository::class);

        $parent = new Folder();
        $node   = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $user
            , $parent
        );
        $edge   = $credentialService->insertCredential($node, $parent);
        $node   = $edge->getNode();

        $publicShare = $shareService->createPublicShare($node);
        $publicShare->setExpireTs((new \DateTime('-10 days')));
        $publicShare->setNodeId($node->getId());
        $node->setPublicShare($publicShare);
        $node = $shareRepository->shareNode($node);

        $request  = $this->getRequestService()
            ->getRequestWithToken($user);
        $response = $publicShareSingle->handle($request->withAttribute("hash", $publicShare->getHash()));

        $data = $this->getResponseService()->getFailedResponseData($response);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
        $this->assertArrayHasKey('message', $data);
        $this->assertTrue($data['message'] === 'no share found');

    }

}
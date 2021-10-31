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

use KSA\PasswordManager\Api\Share\Share;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KST\Service\Service\UserService;
use KST\TestCase;

class ShareTest extends TestCase {

    /**
     * @throws \KSA\PasswordManager\Exception\PasswordManagerException
     */
    public function testShare(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $parent   = new Folder();
        $node     = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $this->getUser()
        );
        $edge     = $credentialService->insertCredential($node, $parent);
        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => UserService::TEST_USER_ID_3
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testShareWithoutNodeId(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);

        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'user_id_to_share' => UserService::TEST_USER_ID_3
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testShareWithoutUserId(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $parent   = new Folder();
        $node     = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $this->getUser()
        );
        $edge     = $credentialService->insertCredential($node, $parent);
        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id' => $node->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testNonShareableSameUser(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $parent   = new Folder();
        $node     = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $this->getUser()
        );
        $edge     = $credentialService->insertCredential($node, $parent);
        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => $this->getUser()->getId()
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testNonShareableLockedUser(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $parent = new Folder();
        $node   = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $this->getUser()
        );

        $edge     = $credentialService->insertCredential($node, $parent);
        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => UserService::TEST_LOCKED_USER_ID_4
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testSharePreviouslyShared(): void {
        /** @var Share $share */
        $share = $this->getServiceManager()->get(Share::class);
        /** @var CredentialService $credentialService */
        $credentialService = $this->getServiceManager()->get(CredentialService::class);

        $parent   = new Folder();
        $node     = $credentialService->createCredential(
            "publicShareSingleTestCredential"
            , "keestash.test"
            , "keestash.test"
            , "Keestash"
            , $this->getUser()
        );
        $edge     = $credentialService->insertCredential($node, $parent);
        $node     = $edge->getNode();
        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => UserService::TEST_USER_ID_3
                ]
            )
        );

        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));

        $response = $share->handle(
            $this->getDefaultRequest(
                [
                    'node_id'            => $node->getId()
                    , 'user_id_to_share' => UserService::TEST_USER_ID_3
                ]
            )
        );

        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }


}
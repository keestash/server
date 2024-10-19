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

namespace Integration\Api\Node\Share\Public;

use Keestash\Core\DTO\Encryption\Credential\Credential;
use Keestash\Core\Service\Encryption\Encryption\KeestashEncryptionService;
use KSA\PasswordManager\Api\Node\Share\Public\PublicShareSingle;
use KSA\PasswordManager\Entity\Share\PublicShare;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class PublicShareSingleTest extends TestCase {

    public function testPublicShareSingle(): void {
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getServiceManager()->get(PublicShareRepository::class);
        /** @var IUserService $userService */
        $userService = $this->getServiceManager()->get(IUserService::class);
        /** @var KeestashEncryptionService $encryptionService */
        $encryptionService = $this->getServiceManager()->get(KeestashEncryptionService::class);

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

        $node = $edge->getNode();

        $password = (string) Uuid::uuid4();
        $c        = new Credential();
        $c->setSecret($password);
        $publicShare = $shareService->createPublicShare(
            $node,
            new \DateTimeImmutable(),
            $userService->hashPassword($password),
            base64_encode($encryptionService->encrypt($c, (string) json_encode(['username' => Uuid::uuid4(), 'password' => Uuid::uuid4()])))
        );

        $node->setPublicShare($publicShare);
        $shareRepository->shareNode($node);

        $request  = $this->getRequestService()
            ->getVirtualRequestWithToken(user: $user, body: ['password' => $password]);
        $response = $publicShareSingle->handle($request->withAttribute("hash", $publicShare->getHash()));

        /** @var LoggerInterface $logger */
        $logger = $this->getService(LoggerInterface::class);
        $logger->debug(PublicShareSingleTest::class . '::testPublicShareSingle', ['response' => (string) $response->getBody()]);
        $this->assertTrue(true === $this->getResponseService()->isValidResponse($response));
    }

    public function testPublicShareWithoutHash(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        $request           = $this->getRequestService()
            ->getVirtualRequestWithToken($user);

        $response = $publicShareSingle->handle($request);
        $this->assertTrue(false === $this->getResponseService()->isValidResponse($response));
    }

    public function testExpiredPublicShare(): void {
        $user = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        /** @var PublicShareSingle $publicShareSingle */
        $publicShareSingle = $this->getServiceManager()->get(PublicShareSingle::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getServiceManager()->get(PublicShareRepository::class);

        $edge = $this->createAndInsertCredential(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
            , $user
            , $this->getRootFolder($user)
        );
        $node = $edge->getNode();

        $publicShare = $shareService->createPublicShare(
            $node,
            new \DateTimeImmutable(),
            (string) Uuid::uuid4(),
            (string) Uuid::uuid4()
        );
        $publicShare = new PublicShare(
            $publicShare->getId(),
            $publicShare->getNodeId(),
            $publicShare->getHash(),
            (new \DateTime('-10 days')),
            (string) Uuid::uuid4(),
            (string) Uuid::uuid4()
        );
        $node->setPublicShare($publicShare);
        $shareRepository->shareNode($node);

        $request  = $this->getRequestService()
            ->getVirtualRequestWithToken($user);
        $response = $publicShareSingle->handle($request->withAttribute("hash", $publicShare->getHash()));

        $this->assertEquals(IResponse::NOT_FOUND, $response->getStatusCode());
    }

}

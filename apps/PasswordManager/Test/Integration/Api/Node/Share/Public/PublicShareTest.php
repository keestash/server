<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

use DateTime;
use DateTimeImmutable;
use KSA\PasswordManager\Api\Node\Share\Public\PublicShare;
use KSA\PasswordManager\ConfigProvider;
use KSA\PasswordManager\Entity\IResponseCodes;
use KSA\PasswordManager\Repository\PublicShareRepository;
use KSA\PasswordManager\Service\Node\Credential\CredentialService;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSA\PasswordManager\Test\Integration\TestCase;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Api\IResponse;
use KSP\Api\IVerb;
use Ramsey\Uuid\Uuid;

/**
 *  TODO test the case where the node does not belong to the user
 *           or the node is not part of the organization
 */
class PublicShareTest extends TestCase {

    public function testEmptyPayload(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
                    , []
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::BAD_REQUEST, $response);

        $body = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertEquals(IResponseCodes::RESPONSE_CODE_NODE_SHARE_PUBLIC_INVALID_PAYLOAD, $body['responseCode']);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testNoNodeFound(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
                    , [
                        "node_id" => "blablablub"
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::NOT_FOUND, $response);

        $body = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertEquals(IResponseCodes::RESPONSE_CODE_NODE_SHARE_PUBLIC_NOT_FOUND, $body['responseCode']);
        $this->logout($headers, $user);
        $this->removeUser($user);
    }

    public function testShareNode(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);

        $credential = $credentialService->createCredential(
            "thisIsThePasswordForPublicShare",
            'https://keestash.com',
            'the-username',
            'the-title',
            $user
        );
        $rootFolder = $this->getRootFolder($user);
        $edge       = $credentialService->insertCredential($credential, $rootFolder);

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
                    , [
                        "node_id" => $edge->getNode()->getId()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);

        $body = (array) json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->assertArrayHasKey('share', $body);
        $this->logout($headers, $user);
        $credentialService->removeCredential($credential);
        $this->removeUser($user);
    }

    /**
     * @param array $params
     * @param bool  $isValid
     * @dataProvider provideData
     */
    public function testPublicShareInvalidNode(array $params, bool $isValid): void {
        /** @var PublicShare $publicShare */
        $publicShare = $this->getServiceManager()->get(PublicShare::class);
        /** @var RequestService $requestService */
        $requestService = $this->getServiceManager()->get(RequestService::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);
        /** @var ShareService $shareService */
        $shareService = $this->getServiceManager()->get(ShareService::class);

        $user    = $this->createUser(
            Uuid::uuid4()->toString()
            , Uuid::uuid4()->toString()
        );
        $request = $requestService->getVirtualRequestWithToken(
            $user
            , []
            , []
            , $params
        );

        $response = $publicShare->handle($request);
        $this->assertTrue($isValid === $responseService->isValidResponse($response));

        if (false === $isValid) return;
        $data = $responseService->getResponseData($response);
        $this->assertArrayHasKey('share', $data);
        $this->assertArrayHasKey('id', $data['share']);
        $this->assertArrayHasKey('hash', $data['share']);
        $this->assertArrayHasKey('expire_ts', $data['share']);
        $this->assertArrayHasKey('is_expired', $data['share']);
        $this->assertArrayHasKey('node_id', $data['share']);

        $this->assertIsInt($data['share']['id']);
        $this->assertIsString($data['share']['hash']);
        $this->assertIsBool($data['share']['is_expired']);
        $this->assertIsInt($data['share']['node_id']);

        $this->assertTrue(false === $data['share']['is_expired']);
        $this->assertTrue(
            $shareService->getDefaultExpireDate()->format('d.m.Y')
            === (new DateTime($data['share']['expire_ts']['date']))->format('d.m.Y')
        );
    }

    public function testAlreadySharedNode(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        /** @var ShareService $shareService */
        $shareService = $this->getService(ShareService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getService(PublicShareRepository::class);

        $credential = $credentialService->createCredential(
            "thisIsThePasswordForPublicShare",
            'https://keestash.com',
            'the-username',
            'the-title',
            $user
        );
        $credential->setPublicShare(
            $shareService->createPublicShare($credential, new DateTimeImmutable(), (string) Uuid::uuid4())
        );

        $rootFolder = $this->getRootFolder($user);
        $edge       = $credentialService->insertCredential($credential, $rootFolder);


        $sharedNode = $shareRepository->shareNode($edge->getNode());

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
                    , [
                        "node_id" => $sharedNode->getId()
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::CONFLICT, $response);

        $body = json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

//        $this->assertEquals(IResponseCodes::RESPONSE_CODE_NODE_SHARE_PUBLIC_NO_SHARE_EXISTS, $body['responseCode']);
        $this->logout($headers, $user);
        $credentialService->removeCredential($credential);
        $this->removeUser($user);
    }

    public function testSharePreviouslySharedAndExpired(): void {
        $password = Uuid::uuid4()->toString();
        $user     = $this->createUser(
            Uuid::uuid4()->toString()
            , $password
        );

        /** @var CredentialService $credentialService */
        $credentialService = $this->getService(CredentialService::class);
        /** @var ShareService $shareService */
        $shareService = $this->getService(ShareService::class);
        /** @var PublicShareRepository $shareRepository */
        $shareRepository = $this->getService(PublicShareRepository::class);

        $credential = $credentialService->createCredential(
            "thisIsThePasswordForPublicShare",
            'https://keestash.com',
            'the-username',
            'the-title',
            $user
        );
        $credential->setPublicShare(
            new \KSA\PasswordManager\Entity\Share\PublicShare(
                0,
                $credential->getId(),
                Uuid::uuid4()->toString(),
                (new DateTimeImmutable())->modify('-100 day'),
                (string) Uuid::uuid4()
            )
        );

        $rootFolder = $this->getRootFolder($user);
        $edge       = $credentialService->insertCredential($credential, $rootFolder);

        $sharedNode = $shareRepository->shareNode($edge->getNode());

        $headers  = $this->login($user, $password);
        $response = $this->getApplication()
            ->handle(
                $this->getRequest(
                    IVerb::POST
                    , ConfigProvider::PASSWORD_MANAGER_PUBLIC_SHARE_PUBLIC
                    , [
                        "node_id" => $sharedNode->getId(),
                    ]
                    , $user
                    , $headers
                )
            );

        $this->assertStatusCode(IResponse::OK, $response);

        $body = json_decode(
            (string) $response->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );

        $this->logout($headers, $user);
        $credentialService->removeCredential($credential);
        $this->removeUser($user);
    }

    public static function provideData(): array {
        return [
            [['node_id' => 845763], false]
            , [[], false]
        ];
    }

}

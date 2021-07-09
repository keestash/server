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

use DateTime;
use KSA\PasswordManager\Api\Share\PublicShare;
use KSA\PasswordManager\Service\Node\Share\ShareService;
use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KST\TestCase;

/**
 * Class PublicShareTest
 * @package KSA\PasswordManager\Test\Integration\Api\Share
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 * TODO test the case where the node does not belong to the user
 *          or the node is not part of the organization
 */
class PublicShareTest extends TestCase {

    public function provideData(): array {
        return [
            [['node_id' => 845763], false]
            , [['node_id' => 2], true]
            , [[], false]
        ];
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

        $request = $requestService->getRequestWithToken(
            $this->getUser()
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

}
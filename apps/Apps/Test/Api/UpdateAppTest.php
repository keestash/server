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

namespace KSA\Apps\Test\Api;

use KSA\Apps\Api\UpdateApp;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KST\TestCase;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\ServerRequest;

class UpdateAppTest extends TestCase {

    public function testHandle(): void {
        /** @var UpdateApp $updateApp */
        $updateApp = $this->getServiceManager()->get(UpdateApp::class);
        /** @var ResponseService $responseService */
        $responseService = $this->getServiceManager()->get(ResponseService::class);
        /** @var JsonResponse $response */
        $response = $updateApp->handle(new ServerRequest());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue(false === $responseService->isValidResponse($response));

        /** @var JsonResponse $response */
        $response = $updateApp->handle(
            new ServerRequest(
                []
                , []
                , null
                , null
                , 'php://input'
                , []
                , []
                , []
                , [
                    'app_id'     => 1
                    , 'activate' => "true"
                ]
            )
        );

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertTrue(false === $responseService->isValidResponse($response));

    }

}
<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KST;

use KSA\PasswordManager\Test\Service\RequestService;
use KSA\PasswordManager\Test\Service\ResponseService;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use Laminas\ServiceManager\ServiceManager;
use PHPUnit\Framework\TestCase as FrameworkTestCase;
use Psr\Http\Message\ServerRequestInterface;

class TestCase extends FrameworkTestCase {

    private ServiceManager  $serviceManager;
    private ResponseService $responseService;
    private RequestService  $requestService;

    protected function getServiceManager(): ServiceManager {
        return $this->serviceManager;
    }

    protected function getService(string $name) {
        return $this->getServiceManager()->get($name);
    }

    protected function setUp(): void {
        parent::setUp();
        $this->serviceManager  = require __DIR__ . '/config/service_manager.php';
        $this->responseService = $this->serviceManager->get(ResponseService::class);
        $this->requestService  = $this->serviceManager->get(RequestService::class);
    }

    protected function getResponseService(): ResponseService {
        return $this->responseService;
    }

    protected function getRequestService(): RequestService {
        return $this->requestService;
    }

    protected function getUser(): IUser {
        return $this->serviceManager->get(IUserRepository::class)
            ->getUserById((string) Service\Service\UserService::TEST_USER_ID_2);
    }

    protected function getDefaultRequest(array $body = []): ServerRequestInterface {
        return $this->getRequestService()->getRequestWithToken(
            $this->getUser()
            , []
            , []
            , $body
        );
    }

}

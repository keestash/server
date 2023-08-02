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

namespace Keestash\Middleware;

use Keestash\Core\Service\Instance\InstallerService;
use KSP\Api\IRequest;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Router\IRouterService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class ApplicationStartedMiddleware implements MiddlewareInterface {

    private IRouterService      $routerService;
    private IEnvironmentService $environmentService;
    private InstallerService    $installerService;
    private IConfigService      $configService;
    private LoggerInterface     $logger;

    public function __construct(
        IRouterService        $routerService
        , IEnvironmentService $environmentService
        , InstallerService    $installerService
        , IConfigService      $configService
        , LoggerInterface     $logger
    ) {
        $this->routerService      = $routerService;
        $this->environmentService = $environmentService;
        $this->installerService   = $installerService;
        $this->configService      = $configService;
        $this->logger             = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_APPLICATION_START
            , microtime(true)
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_MATCHED_PATH
            , $this->routerService->getMatchedPath($request)
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_IS_PUBLIC
            , $this->routerService->isPublicRoute($request)
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_ENVIRONMENT
            , $this->environmentService->getEnv()
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_INSTANCE_ID_AND_HASH_GIVEN
            , $this->installerService->hasIdAndHash()
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_DEBUG
            , $this->configService->getValue('debug', false)
        );
        $request = $request->withAttribute(
            IRequest::ATTRIBUTE_NAME_REQUEST_ID
            , Uuid::uuid4()->toString()
        );
        return $handler->handle($request);
    }

}
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

use Keestash\ConfigProvider;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InstanceInstalledMiddleware implements MiddlewareInterface {

    private HTTPService    $httpService;
    private InstanceDB     $instanceDB;
    private LockHandler    $lockHandler;
    private ILogger        $logger;
    private Config         $config;
    private IRouterService $routerService;

    public function __construct(
        HTTPService $httpService
        , InstanceDB $instanceDB
        , LockHandler $lockHandler
        , ILogger $logger
        , Config $config
        , IRouterService $routerService
    ) {
        $this->httpService   = $httpService;
        $this->instanceDB    = $instanceDB;
        $this->lockHandler   = $lockHandler;
        $this->logger        = $logger;
        $this->config        = $config;
        $this->routerService = $routerService;
    }

    private function routesToInstallation(ServerRequestInterface $request): bool {
        $currentRoute       = $this->routerService->getMatchedPath($request);
        $installationRoutes = $this->config
            ->get(ConfigProvider::INSTALL_INSTANCE_ROUTES)
            ->toArray();

        foreach ($installationRoutes as $publicRoute) {
            if ($currentRoute === $publicRoute) {
                return true;
            }
        }

        return false;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $isLocked             = $this->lockHandler->isLocked();
        $routesToInstallation = $this->routesToInstallation($request);
        $instanceHash         = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_HASH);
        $instanceId           = $this->instanceDB->getOption(InstanceDB::OPTION_NAME_INSTANCE_ID);

        if (true === $isLocked && true === $routesToInstallation) {
            return $handler->handle($request);
        }

        if ((null === $instanceHash || null === $instanceId)) {
            $this->logger->debug("The whole application is not installed. Please Install");
            $this->lockHandler->lock();
            return new RedirectResponse(
                $this->httpService->buildWebRoute(
                    (string) $this->config->get(ConfigProvider::INSTALL_INSTANCE_ROUTE)
                )
            );
        }

        return $handler->handle($request);
    }

}
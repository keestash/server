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

namespace Keestash\Core\Service\Router;

use Keestash\ConfigProvider;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

class RouterService implements IRouterService {

    private RouterInterface     $router;
    private Config              $config;
    private LoggerInterface     $logger;
    private IEnvironmentService $environmentService;

    public function __construct(
        RouterInterface       $router
        , Config              $config
        , LoggerInterface     $logger
        , IEnvironmentService $environmentService
    ) {
        $this->router             = $router;
        $this->config             = $config;
        $this->logger             = $logger;
        $this->environmentService = $environmentService;
    }

    public function getMatchedPath(ServerRequestInterface $request): string {
        $matchedRoute = $this->router->match($request)->getMatchedRoute();

        if ($matchedRoute instanceof Route) {
            return $matchedRoute->getPath();
        }
        return '';
    }

    public function getRouteByPath(string $path): array {
        /** @var Config $webRoutes */
        $webRoutes = $this->config->get(ConfigProvider::WEB_ROUTER);
        /** @var Config $apiRoutes */
        $apiRoutes = $this->config->get(ConfigProvider::API_ROUTER);
        $allRoutes = array_merge_recursive($webRoutes->toArray(), $apiRoutes->toArray());

        foreach ($allRoutes[ConfigProvider::ROUTES] as $route) {
            if ($route['path'] === $path) {
                return $route;
            }
        }
        return [];
    }

    public function getUri(string $name): string {
        return $this->router->generateUri($name);
    }

    public function isPublicRoute(ServerRequestInterface $request): bool {
        $path   = $this->getMatchedPath($request);
        $routes = $this->config
            ->get(ConfigProvider::API_ROUTER)
            ->get(ConfigProvider::PUBLIC_ROUTES)
            ->toArray();
        return in_array($path, $routes, true);
    }

}
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
use Keestash\Exception\KeestashException;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouterService implements IRouterService {

    private RouterInterface     $router;
    private Config              $config;
    private ILogger             $logger;
    private IEnvironmentService $environmentService;

    public function __construct(
        RouterInterface       $router
        , Config              $config
        , ILogger             $logger
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
        $webRoutes = $this->config->get(ConfigProvider::WEB_ROUTER);
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

    public function routesToInstallation(
        ServerRequestInterface $request
        , bool                 $withInstance = true
        , bool                 $withApps = true
    ): bool {
        $currentRoute       = $this->getMatchedPath($request);
        $installationRoutes = [];

        if (true === $withInstance) {
            $installationRoutes = array_merge(
                $installationRoutes
                , $this->config
                ->get(ConfigProvider::INSTALL_APPS_ROUTES)
                ->toArray()
            );
        }

        if (true === $withApps) {
            $installationRoutes = array_merge(
                $installationRoutes
                , $this->config
                ->get(ConfigProvider::INSTALL_INSTANCE_ROUTES)
                ->toArray()
            );
        }

        foreach ($installationRoutes as $publicRoute) {
            if ($currentRoute === $publicRoute) {
                return true;
            }
        }

        return false;
    }

    public function isPublicRoute(ServerRequestInterface $request): bool {
        $path   = $this->getMatchedPath($request);

        switch ($this->environmentService->getEnv()) {
            case ConfigProvider::ENVIRONMENT_WEB:
                $routes = $this->config
                    ->get(ConfigProvider::WEB_ROUTER)
                    ->get(ConfigProvider::PUBLIC_ROUTES)
                    ->toArray();
                break;
            case ConfigProvider::ENVIRONMENT_API:
                $routes = $this->config
                    ->get(ConfigProvider::API_ROUTER)
                    ->get(ConfigProvider::PUBLIC_ROUTES)
                    ->toArray();
                break;
            default:
                throw new KeestashException('unknown environment ' . $this->environmentService->getEnv());
        }

        return in_array($path, $routes, true);
    }

}
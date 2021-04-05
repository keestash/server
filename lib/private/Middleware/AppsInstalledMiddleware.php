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

use Keestash\App\Config\Diff;
use Keestash\ConfigProvider;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\App\LockHandler as AppLockHandler;
use Keestash\Core\System\Installation\Instance\LockHandler as InstanceLockHandler;
use KSP\App\ILoader;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\JsonResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppsInstalledMiddleware implements MiddlewareInterface {

    private InstanceLockHandler $lockHandler;
    private ILoader             $loader;
    private IAppRepository      $appRepository;
    private AppLockHandler      $appLockHandler;
    private Config              $config;
    private RouterInterface     $router;
    private IEnvironmentService $environmentService;
    private HTTPService         $httpService;
    private Diff                $diff;

    public function __construct(
        InstanceLockHandler $lockHandler
        , ILoader $loader
        , IAppRepository $appRepository
        , AppLockHandler $appLockHandler
        , Config $config
        , RouterInterface $router
        , IEnvironmentService $environmentService
        , HTTPService $httpService
        , Diff $diff
    ) {
        $this->lockHandler        = $lockHandler;
        $this->loader             = $loader;
        $this->appRepository      = $appRepository;
        $this->appLockHandler     = $appLockHandler;
        $this->config             = $config;
        $this->router             = $router;
        $this->environmentService = $environmentService;
        $this->httpService        = $httpService;
        $this->diff               = $diff;
    }

    private function routesToInstallation(ServerRequestInterface $request): bool {
        $currentRoute       = $this->getMatchedPath($request);
        $installationRoutes = $this->config
            ->get(ConfigProvider::INSTALL_APPS_ROUTES)
            ->toArray();

        foreach ($installationRoutes as $publicRoute) {
            if ($currentRoute === $publicRoute) {
                return true;
            }
        }

        return false;
    }

    private function getMatchedPath(ServerRequestInterface $request): string {
        $matchedRoute = $this->router->match($request)->getMatchedRoute();

        if ($matchedRoute instanceof Route) {
            return $this->router->match($request)->getMatchedRoute()->getPath();
        }
        return '';
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $instanceLocked = $this->lockHandler->isLocked();

        // if we are actually installing the instance,
        // we need to make sure that Keestash does not want
        // to Install the apps
        if (true === $instanceLocked) {
            $handler->handle($request);
        }

        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        $diff          = $this->diff;
        $appsToInstall = $diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 1: we check if we have new apps to Install
        if ($appsToInstall->size() > 0) {
            return $this->handleNeedsUpgrade($request, $handler);
        }

        // Step 2: we remove all apps that are disabled in our db
        $loadedApps = $diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        if ($appsToUpgrade->size() > 0) {
            return $this->handleNeedsUpgrade($request, $handler);
        }

        return $handler->handle($request);
    }

    private function handleNeedsUpgrade(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $appsLocked           = $this->lockHandler->isLocked();
        $routesToInstallation = $this->routesToInstallation($request);

        if (true === $appsLocked && true === $routesToInstallation) {
            return $handler->handle($request);
        }

        // in this case, we redirect to the Install page
        // since the user is logged in and is in web mode
        if (true === $this->environmentService->isWeb()) {
            $this->appLockHandler->lock();
            return new RedirectResponse(
                $this->httpService->buildWebRoute(
                    ConfigProvider::INSTALL_APPS_ROUTE
                )
            );
        }

        // in all other cases, we simply return an
        // "need to upgrade" JSON String (except for the
        // case where we already route to installation)
        if (
            true === $this->environmentService->isApi()
            && false === $routesToInstallation
        ) {
            return new JsonResponse(
                [
                    'you need to upgrade your apps. Implement me and I will help you'
                ]
            );
        }

        return $handler->handle($request);
    }

}
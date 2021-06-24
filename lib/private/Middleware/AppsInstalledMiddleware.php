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
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppsInstalledMiddleware implements MiddlewareInterface {

    private HTTPService         $httpService;
    private InstanceLockHandler $instanceLockHandler;
    private AppLockHandler      $appLockHandler;
    private Config              $config;
    private ILoader             $loader;
    private IAppRepository      $appRepository;
    private Diff                $diff;
    private IRouterService      $routerService;

    public function __construct(
        HTTPService $httpService
        , InstanceLockHandler $instanceLockHandler
        , Config $config
        , ILoader $loader
        , IAppRepository $appRepository
        , Diff $diff
        , AppLockHandler $appLockHandler
        , IRouterService $routerService
    ) {
        $this->httpService         = $httpService;
        $this->instanceLockHandler = $instanceLockHandler;
        $this->config              = $config;
        $this->loader              = $loader;
        $this->appRepository       = $appRepository;
        $this->diff                = $diff;
        $this->appLockHandler      = $appLockHandler;
        $this->routerService       = $routerService;
    }

    private function routesToInstallation(ServerRequestInterface $request): bool {
        $currentRoute       = $this->routerService->getMatchedPath($request);
        $installationRoutes = array_merge(
            $this->config
                ->get(ConfigProvider::INSTALL_APPS_ROUTES)
                ->toArray()
            , $this->config
            ->get(ConfigProvider::INSTALL_INSTANCE_ROUTES)
            ->toArray()
        );

        foreach ($installationRoutes as $publicRoute) {
            if ($currentRoute === $publicRoute) {
                return true;
            }
        }

        return false;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $instanceLocked       = $this->instanceLockHandler->isLocked();
        $appsLocked           = $this->appLockHandler->isLocked();
        $routesToInstallation = $this->routesToInstallation($request);

        // if we are actually installing the instance,
        // we need to make sure that Keestash does not want
        // to Install the apps
        if (true === $instanceLocked || true === $appsLocked) {

            if (true === $routesToInstallation) {
                return $handler->handle($request);
            }

        }

        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        $appsToInstall = $this->diff->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 1: we check if we have new apps to Install
        if ($appsToInstall->size() > 0) {
            return $this->handleInstall();
        }

        // Step 2: we remove all apps that are disabled in our db
        $loadedApps = $this->diff->removeDisabledApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $this->diff->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        if ($appsToUpgrade->size() > 0) {
            return $this->handleInstall();
        }

        return $handler->handle($request);

    }

    private function handleInstall(): ResponseInterface {
        $this->appLockHandler->lock();
        return new RedirectResponse(
            $this->httpService->buildWebRoute('install')
        );
    }

}
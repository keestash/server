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

use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\App\LockHandler as AppLockHandler;
use KSP\Api\IRequest;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AppsInstalledMiddleware implements MiddlewareInterface {

    private HTTPService    $httpService;
    private AppLockHandler $appLockHandler;
    private ILoaderService $loader;
    private IAppRepository $appRepository;
    private IAppService    $appService;

    public function __construct(
        HTTPService      $httpService
        , ILoaderService $loader
        , IAppRepository $appRepository
        , AppLockHandler $appLockHandler
        , IAppService    $appService
    ) {
        $this->httpService    = $httpService;
        $this->loader         = $loader;
        $this->appRepository  = $appRepository;
        $this->appLockHandler = $appLockHandler;
        $this->appService     = $appService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $appsLocked           = $this->appLockHandler->isLocked();
        $routesToInstallation = $request->getAttribute(IRequest::ATTRIBUTE_NAME_ROUTES_TO_INSTALL, false);

        /**
         * !!!! ORDER MATTERS HERE !!!!
         *
         * This middleware has to run after InstanceInstalledMiddleware::class since
         * we can only install apps when the instance is installed.
         */

        // if we are actually installing the instance,
        // we need to make sure that Keestash does not want
        // to Install the apps
        if (true === $appsLocked || true === $routesToInstallation) {
            return $handler->handle($request);
        }

        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        $appsToInstall = $this->appService->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 1: we check if we have new apps to Install
        if ($appsToInstall->size() > 0) {
            return $this->handleInstall();
        }

        // Step 2: we remove all apps that are disabled in our db
        $loadedApps = $this->appService->removeDisabledApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $this->appService->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

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
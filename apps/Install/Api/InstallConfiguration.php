<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\Install\Api;

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class InstallConfiguration implements RequestHandlerInterface {

    public const INSTALL_TYPE_NOTHING_TO_UPDATE        = 0;
    public const INSTALL_TYPE_INSTALL_APPS             = 1;
    public const INSTALL_TYPE_UPDATE_APPS              = 2;
    public const INSTALL_TYPE_INSTALL_AND_UPGRADE_APPS = 3;
    public const INSTALL_TYPE_ERROR                    = 4;

    private ILoaderService $loader;
    private IAppRepository $appRepository;
    private IAppService    $appService;

    public function __construct(
        ILoaderService   $loader
        , IAppRepository $appRepository
        , IAppService    $appService
    ) {
        $this->loader        = $loader;
        $this->appRepository = $appRepository;
        $this->appService    = $appService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return new JsonResponse([],IResponse::NOT_FOUND);
        // We only check loadedApps if the system is
        // installed
        $loadedApps    = $this->loader->getApps();
        $installedApps = $this->appRepository->getAllApps();

        // Step 1: we remove all apps that are disabled in our db
        $loadedApps = $this->appService->removeDisabledApps($loadedApps, $installedApps);

        // Step 2: we determine all apps that needs to be installed
        $appsToInstall = $this->appService->getNewlyAddedApps($loadedApps, $installedApps);

        // Step 3: we check if one of our loaded apps has a new version
        // at this point, we can be sure that both maps contain the same
        // apps
        $appsToUpgrade = $this->appService->getAppsThatNeedAUpgrade($loadedApps, $installedApps);

        return new JsonResponse(
            [
                "appsToInstall"  => $appsToInstall->toArray()
                , "appsToUpdate" => $appsToUpgrade->toArray()
                , "installType"  => $this->getInstallType($appsToInstall, $appsToUpgrade)
            ],
            IResponse::OK
        );
    }

    private function getInstallType(HashTable $appsToInstall, HashTable $appsToUpgrade): int {
        if (0 === $appsToInstall->size() && 0 === $appsToUpgrade->size()) return InstallConfiguration::INSTALL_TYPE_NOTHING_TO_UPDATE;
        if ($appsToInstall->size() > 0 && 0 === $appsToUpgrade->size()) return InstallConfiguration::INSTALL_TYPE_INSTALL_APPS;
        if (0 === $appsToInstall->size() && $appsToUpgrade->size() > 0) return InstallConfiguration::INSTALL_TYPE_UPDATE_APPS;
        if ($appsToInstall->size() > 0 && $appsToUpgrade->size() > 0) return InstallConfiguration::INSTALL_TYPE_INSTALL_AND_UPGRADE_APPS;
        return InstallConfiguration::INSTALL_TYPE_ERROR;
    }


}
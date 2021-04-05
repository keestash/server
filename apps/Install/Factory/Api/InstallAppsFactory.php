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

namespace KSA\Install\Factory\Api;

use Keestash\App\Config\Diff;
use Keestash\Core\Service\App\InstallerService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\System\Installation\App\LockHandler;
use KSA\Install\Api\InstallApps;
use KSP\App\ILoader;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Psr\Container\ContainerInterface;

class InstallAppsFactory {

    public function __invoke(ContainerInterface $container): InstallApps {
        return new InstallApps(
            $container->get(InstallerService::class)
            , $container->get(LockHandler::class)
            , $container->get(HTTPService::class)
            , $container->get(ILogger::class)
            , $container->get(ILoader::class)
            , $container->get(IAppRepository::class)
            , $container->get(Diff::class)
        );
    }

}
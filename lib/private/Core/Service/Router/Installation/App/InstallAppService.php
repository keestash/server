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

namespace Keestash\Core\Service\Router\Installation\App;

use doganoo\PHPUtil\Util\ClassUtil;
use Exception;
use Keestash;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Exception\KeestashException;
use KSP\Core\ILogger\ILogger;

class InstallAppService {

    private const ROUTE_INSTALL          = "install";
    private const ROUTE_INSTALL_ALL_APPS = "install/apps/all/";

    private ILogger $logger;

    public function __construct(ILogger $logger) {
        $this->logger = $logger;
    }

    public function routesToInstallation(): bool {
        $router               = Keestash::getServer()->getRouter();
        $className            = ClassUtil::getClassName($router);
        $routesToInstallation = false;

        switch ($className) {
            case HTTPRouter::class:
                /** @var HTTPRouter $router */
                $routesToInstallation = $this->handleHttp($router);
                break;
            case APIRouter::class:
                /** @var APIRouter $router */
                $routesToInstallation = $this->handleApi($router);
                break;
            default:
                throw new KeestashException("could not identify class");
        }

        return $routesToInstallation;
    }

    private function handleHttp(HTTPRouter $router): bool {
        if (false === $router->hasRoute(InstallAppService::ROUTE_INSTALL)) return false;
        if ($router->getRouteName() !== InstallAppService::ROUTE_INSTALL) return false;
        return true;
    }

    private function handleApi(APIRouter $router) {

        try {
            $name = $router->getRouteName();
            return in_array(
                $name
                , [
                    InstallAppService::ROUTE_INSTALL_ALL_APPS
                ]
            );
        } catch (Exception $e) {
            // we do not do anything here
            // we know that there is no route and
            // return false
            $this->logger->debug($e->getTraceAsString());
            return false;
        }

    }

}

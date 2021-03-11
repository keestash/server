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

namespace Keestash\Core\Service\Router\Installation\Instance;

use doganoo\PHPUtil\Util\ClassUtil;
use Exception;
use Keestash;
use Keestash\Core\Manager\RouterManager\Router\APIRouter;
use Keestash\Core\Manager\RouterManager\Router\HTTPRouter;
use Keestash\Exception\KeestashException;
use KSP\Core\ILogger\ILogger;

class InstallInstanceService {

    private const ROUTE_INSTALL_INSTANCE               = "install_instance";
    private const ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG = "install_instance/update_config/";
    private const ROUTE_INSTALL_INSTANCE_DIRS_WRITABLE = "install_instance/dirs_writable/";
    private const ROUTE_INSTALL_INSTANCE_CONFIG_DATA   = "install_instance/config_data/";
    private const ROUTE_INSTALL_INSTANCE_END_UPDATE    = "install_instance/end_update/";
    private const ROUTE_INSTALL_INSTANCE_HAS_DATA_DIRS = "install_instance/has_data_dirs/";
    private const ROUTE_LOGIN_SUBMIT                   = "login/submit/";
    private const ROUTE_FRONTEND_TEMPLATES_ALL         = "frontend_templates/all/";
    private const ROUTE_FRONTEND_STRINGS_ALL           = "frontend_strings/all/";

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
        $this->logger->debug("routename: " . $router->getRouteName());
        if (false === $router->hasRoute(InstallInstanceService::ROUTE_INSTALL_INSTANCE)) return false;
        if ($router->getRouteName() !== InstallInstanceService::ROUTE_INSTALL_INSTANCE) return false;
        return true;
    }

    private function handleApi(APIRouter $router): bool {

        try {
            $name = $router->getRouteName();
            $this->logger->debug("routename: " . $router->getRouteName());
            return in_array(
                $name
                , [
                    InstallInstanceService::ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG
                    , InstallInstanceService::ROUTE_INSTALL_INSTANCE_DIRS_WRITABLE
                    , InstallInstanceService::ROUTE_INSTALL_INSTANCE_CONFIG_DATA
                    , InstallInstanceService::ROUTE_INSTALL_INSTANCE_END_UPDATE
                    , InstallInstanceService::ROUTE_INSTALL_INSTANCE_HAS_DATA_DIRS
                    , InstallInstanceService::ROUTE_LOGIN_SUBMIT
                    , InstallInstanceService::ROUTE_FRONTEND_TEMPLATES_ALL
                    , InstallInstanceService::ROUTE_FRONTEND_STRINGS_ALL
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

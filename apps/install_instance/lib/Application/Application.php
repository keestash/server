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

namespace KSA\InstallInstance\Application;

use Keestash;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\User\UserService;
use KSA\InstallInstance\Api\Config\Get;
use KSA\InstallInstance\Api\Config\Update;
use KSA\InstallInstance\Api\EndUpdate\EndUpdate;
use KSA\InstallInstance\Command\DemoMode;
use KSA\InstallInstance\Command\Uninstall;
use KSA\InstallInstance\Controller\Controller;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Repository\User\IUserRepository;

class Application extends Keestash\App\Application {

    public const APP_ID = "install_instance";

    public const ROUTE_INSTALL_INSTANCE               = "install_instance";
    public const ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG = "install_instance/update_config/";
    public const ROUTE_INSTALL_INSTANCE_CONFIG_DATA   = "install_instance/config_data/";
    public const ROUTE_INSTALL_INSTANCE_END_UPDATE    = "install_instance/end_update/";

    public const LOG_REQUESTS_ENABLED  = "enabled";
    public const LOG_REQUESTS_DISABLED = "disabled";

    public function register(): void {
        $this->registerRoute(
            Application::ROUTE_INSTALL_INSTANCE
            , Controller::class
        );

        $this->registerApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG
            , Update::class
            , [IRouterManager::POST]
        );

        $this->registerApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_CONFIG_DATA
            , Get::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_END_UPDATE
            , EndUpdate::class
            , [IRouterManager::POST]
        );

        $this->registerPublicRoute(
            Application::ROUTE_INSTALL_INSTANCE
        );
        $this->registerPublicApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_UPDATE_CONFIG
        );

        $this->registerPublicApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_END_UPDATE
        );
        $this->registerPublicApiRoute(
            Application::ROUTE_INSTALL_INSTANCE_CONFIG_DATA
        );

        $this->addJavaScript(Application::APP_ID);
        $this->registerCommands();
    }

    private function registerCommands(): void {
        $this->registerCommand(
            new Uninstall(
                Keestash::getServer()->query(InstanceRepository::class)
            )
        );
        $this->registerCommand(new DemoMode(
            Keestash::getServer()->getInstanceDB()
            , Keestash::getServer()->query(UserService::class)
            , Keestash::getServer()->query(IUserRepository::class)
        ));
    }


}

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
use KSA\InstallInstance\Controller\Controller;

class Application extends Keestash\App\Application {

    public const APP_ID = "InstallInstance";

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

        $this->registerPublicRoute(
            Application::ROUTE_INSTALL_INSTANCE
        );

        $this->addJavaScriptFor(
            Application::APP_ID
            , "install_instance"
            , Application::ROUTE_INSTALL_INSTANCE
        );
    }

}

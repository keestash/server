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

namespace KSA\Apps\Application;

use Keestash;
use KSA\Apps\Api\UpdateApp;
use KSA\Apps\Controller\Controller;
use KSP\Core\Manager\RouterManager\IRouterManager;

class Application extends \Keestash\App\Application {

    public const APPS = "apps";

    public const PERMISSION_READ_APPS = "core_app_permission_read_apps";

    public const UPDATE_APPS = "apps/update";

    public function register(): void {

        parent::registerApiRoute(
            Application::UPDATE_APPS
            , UpdateApp::class
            , [IRouterManager::POST]
        );

        parent::registerRoute(Application::APPS, Controller::class);
        parent::addJavascript("apps");

        parent::addSetting(
            self::APPS
            , Keestash::getServer()
            ->getL10N()
            ->translate("Apps")
            , "fas fa-user-circle"
            , 3
        );
    }

}
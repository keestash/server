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

namespace KSA\Install\Application;

use Keestash;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\Phinx\Migrator;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\App\LockHandler;
use KSA\Install\Api\InstallApps;
use KSA\Install\Command\Uninstall;
use KSA\Install\Controller\Controller;
use KSP\Core\Manager\RouterManager\IRouter;

class Application extends Keestash\App\Application {

    public const INSTALL          = "install";
    public const INSTALL_ALL_APPS = "install/apps/all/";

    public function register(): void {

        $this->registerRoute(
            Application::INSTALL
            , Controller::class
        );

        $this->registerPublicRoute(
            Application::INSTALL
        );

        $this->registerApiRoute(
            Application::INSTALL_ALL_APPS
            , InstallApps::class
            , [
                IRouter::POST
            ]
        );

        $this->registerPublicApiRoute(
            Application::INSTALL_ALL_APPS
        );

        parent::addJavaScript("install");

        $this->registerCommand(
            new Uninstall(
                Keestash::getServer()->query(InstanceRepository::class)
                , Keestash::getServer()->query(LockHandler::class)
                , Keestash::getServer()->query(Migrator::class)
                , Keestash::getServer()->query(UserService::class)
            )
        );

    }

}

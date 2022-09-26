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

use KSA\InstallInstance\Api\Config\Get;
use KSA\InstallInstance\Api\Config\Update;
use KSA\InstallInstance\Api\EndUpdate\EndUpdate;
use KSA\InstallInstance\Command\DemoMode;
use KSA\InstallInstance\Command\Install;
use KSA\InstallInstance\Command\Uninstall;
use KSA\InstallInstance\Controller\Controller;
use KSA\InstallInstance\Factory\Api\Config\GetFactory;
use KSA\InstallInstance\Factory\Api\Config\UpdateFactory;
use KSA\InstallInstance\Factory\Api\EndUpdate\EndUpdateFactory;
use KSA\InstallInstance\Factory\Command\DemoModeFactory;
use KSA\InstallInstance\Factory\Command\InstallFactory;
use KSA\InstallInstance\Factory\Command\UninstallFactory;
use KSA\InstallInstance\Factory\Controller\ControllerFactory;

return [
    'factories' => [
        // api
        Get::class          => GetFactory::class
        , Update::class     => UpdateFactory::class
        , EndUpdate::class  => EndUpdateFactory::class

        // command
        , DemoMode::class   => DemoModeFactory::class
        , Uninstall::class  => UninstallFactory::class
        , Install::class    => InstallFactory::class

        // controller
        , Controller::class => ControllerFactory::class
    ]
];
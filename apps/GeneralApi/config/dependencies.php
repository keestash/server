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

use KSA\GeneralApi\Api\Demo\AddEmailAddress;
use KSA\GeneralApi\Api\MinimumCredential;
use KSA\GeneralApi\Api\Template\GetAll;
use KSA\GeneralApi\Api\UserList;
use KSA\GeneralApi\Command\Migration\MigrateApps;
use KSA\GeneralApi\Command\QualityTool\ClearBundleJS;
use KSA\GeneralApi\Command\QualityTool\PHPStan;
use KSA\GeneralApi\Command\Stylesheet\Compiler;
use KSA\GeneralApi\Controller\Common\DefaultRouteController;
use KSA\GeneralApi\Controller\File\View;
use KSA\GeneralApi\Controller\Route\RouteList;
use KSA\GeneralApi\Factory\Api\Demo\AddEmailAddressFactory;
use KSA\GeneralApi\Factory\Api\MinimumCredentialFactory;
use KSA\GeneralApi\Factory\Api\Template\GetAllFactory;
use KSA\GeneralApi\Factory\Api\Thumbnail\FileFactory;
use KSA\GeneralApi\Factory\Api\UserListFactory;
use KSA\GeneralApi\Factory\Command\ClearBundleJSFactory;
use KSA\GeneralApi\Factory\Command\CompilerFactory;
use KSA\GeneralApi\Factory\Command\MigrateAppsFactory;
use KSA\GeneralApi\Factory\Command\PHPStanFactory;
use KSA\GeneralApi\Factory\Controller\Common\DefaultRouteControllerFactory;
use KSA\GeneralApi\Factory\Controller\File\ViewFactory;
use KSA\GeneralApi\Factory\Controller\Route\RouteListFactory;

return [
    'factories' => [
        // command
        ClearBundleJS::class     => ClearBundleJSFactory::class,
        Compiler::class          => CompilerFactory::class,
        MigrateApps::class       => MigrateAppsFactory::class,
        PHPStan::class           => PHPStanFactory::class,

        // api
        UserList::class          => UserListFactory::class,
        MinimumCredential::class => MinimumCredentialFactory::class,
        AddEmailAddress::class   => AddEmailAddressFactory::class,

        \KSA\GeneralApi\Api\Strings\GetAll::class => \KSA\GeneralApi\Factory\Api\Strings\GetAllFactory::class,
        GetAll::class                             => GetAllFactory::class,
        \KSA\GeneralApi\Api\Thumbnail\File::class => FileFactory::class,
        \KSA\GeneralApi\Api\Thumbnail\Get::class  => \KSA\GeneralApi\Factory\Api\Thumbnail\GetFactory::class,

        // controller
        View::class                               => ViewFactory::class,

        RouteList::class              => RouteListFactory::class,
        DefaultRouteController::class => DefaultRouteControllerFactory::class,
    ],
];
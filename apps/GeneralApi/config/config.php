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

use KSA\GeneralApi\Command\Migration\MigrateApps;
use KSA\GeneralApi\Command\QualityTool\ClearBundleJS;
use KSA\GeneralApi\Command\QualityTool\PHPStan;
use KSA\GeneralApi\Command\Stylesheet\Compiler;
use KSA\GeneralApi\Event\Listener\UserChangedListener;
use KSA\GeneralApi\Event\Organization\UserChangedEvent;
use KSP\App\IApp;

return [
    'dependencies'                   => require __DIR__ . '/dependencies.php',
    IApp::CONFIG_PROVIDER_API_ROUTER => require __DIR__ . '/router.php',
    IApp::CONFIG_PROVIDER_COMMANDS   => [
        MigrateApps::class
        , PHPStan::class
        , ClearBundleJS::class
        , Compiler::class
    ],
    IApp::CONFIG_PROVIDER_EVENTS     => [
        UserChangedEvent::class => UserChangedListener::class
    ]
];
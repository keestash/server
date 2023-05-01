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

use KSA\InstallInstance\Command\Apps\InstallApps;
use KSA\InstallInstance\Command\CreateConfig;
use KSA\InstallInstance\Command\CreateSystemUser;
use KSA\InstallInstance\Command\Environment;
use KSA\InstallInstance\Command\InstanceData;
use KSA\InstallInstance\Command\ListEnvironment;
use KSA\InstallInstance\Command\Permission\CreatePermissions;
use KSA\InstallInstance\Command\Ping;
use KSA\InstallInstance\Command\Role\AssignPermissionsToRoles;
use KSA\InstallInstance\Command\Role\CreateRoles;
use KSA\InstallInstance\Command\Uninstall;

return [
    Uninstall::class
    , Ping::class
    , Environment::class
    , ListEnvironment::class
    , CreateConfig::class
    , CreatePermissions::class
    , CreateSystemUser::class
    , InstanceData::class
    , CreateRoles::class
    , AssignPermissionsToRoles::class
    , InstallApps::class
    , \KSA\InstallInstance\Command\Apps\Uninstall::class
];
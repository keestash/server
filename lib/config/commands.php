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

use Keestash\Command\App\InstallApps;
use Keestash\Command\App\ListAll;
use Keestash\Command\Configuration\ResponseCodes\ListAll as ResponseCodeListAll;
use Keestash\Command\Configuration\ResponseCodes\Verify;
use Keestash\Command\CreateSystemUser;
use Keestash\Command\Derivation\AddDerivation;
use Keestash\Command\Derivation\ClearDerivation;
use Keestash\Command\Derivation\DerivationList;
use Keestash\Command\Environment\Environment;
use Keestash\Command\Environment\ListEnvironment;
use Keestash\Command\Event\ListEvents;
use Keestash\Command\Install\CreateConfig;
use Keestash\Command\Install\InstanceData;
use Keestash\Command\Install\Uninstall;
use Keestash\Command\Permission\Add;
use Keestash\Command\Permission\CreatePermissions;
use Keestash\Command\Permission\Get;
use Keestash\Command\Permission\PermissionsByRole;
use Keestash\Command\Permission\Role\AssignPermissionsToRoles;
use Keestash\Command\Permission\Role\AssignPermissionToRole;
use Keestash\Command\Permission\Role\CreateRoles;
use Keestash\Command\Permission\Role\RemovePermissionFromRole;
use Keestash\Command\Ping;
use Keestash\Command\RateLimit\ClearRateLimiterFile;
use Keestash\Command\Role\AssignRoleToUser;
use Keestash\Command\Role\RolesByUser;
use Keestash\Command\Routes;
use Keestash\Command\Security\Cors;
use Keestash\Command\TestEmail;
use Keestash\Command\Worker\Queue\QueueDelete;
use Keestash\Command\Worker\Queue\QueueList;
use Keestash\Command\Worker\Queue\Reset;
use Keestash\Command\Worker\WorkerFlusher;
use Keestash\Command\Worker\WorkerLocker;
use Keestash\Command\Worker\WorkerRunner;
use Keestash\Command\Worker\WorkerSingleRun;

return [
    WorkerRunner::class
    , Get::class
    , \Keestash\Command\Role\Get::class
    , RolesByUser::class
    , PermissionsByRole::class
    , Add::class
    , \Keestash\Command\Role\Add::class
    , AssignRoleToUser::class
    , AssignPermissionToRole::class
    , ListEvents::class
    , QueueList::class
    , QueueDelete::class
    , Reset::class
    , ListAll::class
    , ClearDerivation::class
    , AddDerivation::class
    , Cors::class
    , DerivationList::class
    , ClearRateLimiterFile::class
    , WorkerLocker::class
    , WorkerFlusher::class
    , Routes::class
    , Uninstall::class
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
    , \Keestash\Command\App\Uninstall::class
    , RemovePermissionFromRole::class
    , WorkerSingleRun::class
    , TestEmail::class
    , ResponseCodeListAll::class
    , Verify::class
];
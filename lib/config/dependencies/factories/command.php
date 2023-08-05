<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use Keestash\Factory\Command\App\InstallAppsFactory;
use Keestash\Factory\Command\App\ListAllFactory;
use Keestash\Factory\Command\Configuration\ResponseCodes\ListAllFactory as ResponseCodeListAllFactory;
use Keestash\Factory\Command\Configuration\ResponseCodes\VerifyFactory;
use Keestash\Factory\Command\CreateSystemUserFactory;
use Keestash\Factory\Command\Derivation\AddDerivationFactory;
use Keestash\Factory\Command\Derivation\ClearDerivationFactory;
use Keestash\Factory\Command\Derivation\DerivationListFactory;
use Keestash\Factory\Command\Environment\EnvironmentFactory;
use Keestash\Factory\Command\Environment\ListEnvironmentFactory;
use Keestash\Factory\Command\Event\EventsFactory;
use Keestash\Factory\Command\Install\CreateConfigFactory;
use Keestash\Factory\Command\Install\InstanceDataFactory;
use Keestash\Factory\Command\Install\UninstallFactory;
use Keestash\Factory\Command\Permission\AddFactory;
use Keestash\Factory\Command\Permission\CreatePermissionsFactory;
use Keestash\Factory\Command\Permission\GetFactory;
use Keestash\Factory\Command\Permission\PermissionsByRoleFactory;
use Keestash\Factory\Command\Permission\Role\AssignPermissionsToRolesFactory;
use Keestash\Factory\Command\Permission\Role\AssignPermissionToRoleFactory;
use Keestash\Factory\Command\Permission\Role\CreateRolesFactory;
use Keestash\Factory\Command\Permission\Role\RemovePermissionFromRoleFactory;
use Keestash\Factory\Command\RateLimit\ClearRateLimiterFileFactory;
use Keestash\Factory\Command\Role\AssignRoleToUserFactory;
use Keestash\Factory\Command\Role\RolesByUserFactory;
use Keestash\Factory\Command\RoutesFactory;
use Keestash\Factory\Command\Security\CorsFactory;
use Keestash\Factory\Command\TestEmailFactory;
use Keestash\Factory\Command\Worker\Queue\QueueDeleteFactory;
use Keestash\Factory\Command\Worker\Queue\QueueListFactory;
use Keestash\Factory\Command\Worker\Queue\ResetFactory;
use Keestash\Factory\Command\Worker\WorkerFlusherFactory;
use Keestash\Factory\Command\Worker\WorkerLockerFactory;
use Keestash\Factory\Command\Worker\WorkerRunnerFactory;
use Keestash\Factory\Command\Worker\WorkerSingleRunFactory;
use Laminas\ServiceManager\Factory\InvokableFactory;

return [
    // command
    Get::class                               => GetFactory::class
    , \Keestash\Command\Role\Get::class      => \Keestash\Factory\Command\Role\GetFactory::class
    , RolesByUser::class                     => RolesByUserFactory::class
    , PermissionsByRole::class               => PermissionsByRoleFactory::class
    , Add::class                             => AddFactory::class
    , \Keestash\Command\Role\Add::class      => \Keestash\Factory\Command\Role\AddFactory::class
    , AssignRoleToUser::class                => AssignRoleToUserFactory::class
    , AssignPermissionToRole::class          => AssignPermissionToRoleFactory::class
    , WorkerRunner::class                    => WorkerRunnerFactory::class
    , ListEvents::class                      => EventsFactory::class
    , QueueList::class                       => QueueListFactory::class
    , QueueDelete::class                     => QueueDeleteFactory::class
    , Reset::class                           => ResetFactory::class
    , ListAll::class                         => ListAllFactory::class
    , ClearDerivation::class                 => ClearDerivationFactory::class
    , AddDerivation::class                   => AddDerivationFactory::class
    , Cors::class                            => CorsFactory::class
    , DerivationList::class                  => DerivationListFactory::class
    , ClearRateLimiterFile::class            => ClearRateLimiterFileFactory::class
    , WorkerLocker::class                    => WorkerLockerFactory::class
    , WorkerFlusher::class                   => WorkerFlusherFactory::class
    , Routes::class                          => RoutesFactory::class
    , Uninstall::class                       => UninstallFactory::class
    , Ping::class                            => InvokableFactory::class
    , Environment::class                     => EnvironmentFactory::class
    , ListEnvironment::class                 => ListEnvironmentFactory::class
    , CreateConfig::class                    => CreateConfigFactory::class
    , CreatePermissions::class               => CreatePermissionsFactory::class
    , CreateSystemUser::class                => CreateSystemUserFactory::class
    , InstanceData::class                    => InstanceDataFactory::class
    , CreateRoles::class                     => CreateRolesFactory::class
    , AssignPermissionsToRoles::class        => AssignPermissionsToRolesFactory::class
    , InstallApps::class                     => InstallAppsFactory::class
    , \Keestash\Command\App\Uninstall::class => \Keestash\Factory\Command\App\UninstallFactory::class
    , RemovePermissionFromRole::class        => RemovePermissionFromRoleFactory::class
    , WorkerSingleRun::class                 => WorkerSingleRunFactory::class
    , TestEmail::class                       => TestEmailFactory::class
    , ResponseCodeListAll::class             => ResponseCodeListAllFactory::class
    , Verify::class                          => VerifyFactory::class
];
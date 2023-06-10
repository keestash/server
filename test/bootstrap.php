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

use Keestash\ConfigProvider;
use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use Keestash\Core\Repository\Instance\InstanceDB;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Permission\IPermissionService;
use KSP\Core\Service\Permission\IRoleService;
use KSP\Core\Service\Phinx\IMigrator;
use KST\Service\Exception\WarningException;
use KST\Service\Service\UserService;
use KST\Service\ThirdParty\Phinx\Adapter\SQLiteAdapter;
use Laminas\Config\Config;
use Phinx\Db\Adapter\AdapterFactory;
use Psr\Container\ContainerInterface;
use KSP\Core\Service\Instance\IInstallerService;

const __PHPUNIT_MODE__ = true;

/** @var ContainerInterface $container */
$container = require __DIR__ . '/config/service_manager.php';

set_error_handler(
    static function (int $errno, string $message): void {
        throw new WarningException($message, $errno);
    }, E_WARNING | E_USER_WARNING
);

$config   = $container->get(Config::class);
$fileName = $config->get(ConfigProvider::TEST_PATH) . '/config/test.unit.keestash.sqlite';

if (is_file($fileName)) {
    unlink($fileName);
}

AdapterFactory::instance()
    ->registerAdapter('sqlite', SQLiteAdapter::class);
/** @var \Doctrine\DBAL\Connection $connection */
$connection = $container->get(\Doctrine\DBAL\Connection::class);
$connection->executeStatement('PRAGMA foreign_keys = ON;');

/** @var IPermissionService $permissionService */
$permissionService = $container->get(IPermissionService::class);
/** @var IRoleService $roleService */
$roleService = $container->get(IRoleService::class);
/** @var IEnvironmentService $environmentService */
$environmentService = $container->get(IEnvironmentService::class);
/** @var IInstallerService $installerService */
$installerService = $container->get(IInstallerService::class);
/** @var InstanceDB $instanceDb */
$instanceDb = $container->get(InstanceDB::class);

$environmentService->setEnv(ConfigProvider::ENVIRONMENT_UNIT_TEST);

if (false === $installerService->hasIdAndHash()) {
    $installerService->writeIdAndHash();
}

$instanceDb->addOption(
    InstanceDB::OPTION_NAME_ENVIRONMENT
    , 'dev'
);
$instanceDb->addOption(
    InstanceDB::OPTION_NAME_SAAS
    , 'false'
);
$instanceDb->addOption(
    InstanceDB::OPTION_NAME_NOTIFICATIONS_SEND_ALLOWED
    , 'false'
);

/** @var IMigrator $migrator */
$migrator = $container->get(IMigrator::class);
$migrator->runCore();
$migrator->runApps();

/** @var IEventService $eventService */
$eventService = $container->get(IEventService::class);
$eventService->registerAll($config->get(ConfigProvider::EVENTS)->toArray());
$eventService->execute(new ApplicationStartedEvent(new DateTimeImmutable()));

/** @var UserService $userService */
$userService = $container->get(UserService::class);

$permissionService->recreatePermissions();
$roleService->recreateRoles();
$userService->createTestUsers();
$roleService->assignAllRoles();

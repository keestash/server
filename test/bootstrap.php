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
use KSP\Core\Service\Phinx\IMigrator;
use KST\Service\Service\UserService;
use KST\Service\ThirdParty\Phinx\Adapter\SQLiteAdapter;
use Laminas\Config\Config;
use Phinx\Db\Adapter\AdapterFactory;
use Psr\Container\ContainerInterface;

const __PHPUNIT_MODE__ = true;

/** @var ContainerInterface $container */
$container = require __DIR__ . '/config/service_manager.php';

$config   = $container->get(Config::class);
$fileName = $config->get(ConfigProvider::TEST_PATH) . '/config/test.unit.keestash.sqlite';

if (is_file($fileName)) {
    unlink($fileName);
}

AdapterFactory::instance()
    ->registerAdapter('sqlite', SQLiteAdapter::class);

/** @var IEnvironmentService $environmentService */
$environmentService = $container->get(IEnvironmentService::class);
$environmentService->setEnv(ConfigProvider::ENVIRONMENT_UNIT_TEST);

/** @var InstanceDB $instanceDb */
$instanceDb = $container->get(InstanceDB::class);
$instanceDb->addOption(
    InstanceDB::OPTION_NAME_ENVIRONMENT
    , 'dev'
);

/** @var IMigrator $migrator */
$migrator = $container->get(IMigrator::class);
$migrator->runCore();
$migrator->runApps();

/** @var IEventService $eventManager */
$eventManager = $container->get(IEventService::class);
$eventManager->registerAll($config->get(ConfigProvider::EVENTS)->toArray());
$eventManager->execute(new ApplicationStartedEvent(new DateTime()));

/** @var UserService $userService */
$userService = $container->get(UserService::class);
$userService->createTestUsers();

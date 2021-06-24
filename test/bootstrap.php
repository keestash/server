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
use Keestash\Core\DTO\User\User;
use Keestash\Legacy\Legacy;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Event\IEventDispatcher;
use KSP\Core\Service\Phinx\IMigrator;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Service\ThirdParty\Phinx\Adapter\SQLiteAdapter;
use Laminas\Config\Config;
use Phinx\Db\Adapter\AdapterFactory;
use Psr\Container\ContainerInterface;

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

/** @var IEventDispatcher $eventDispatcher */
$eventDispatcher = $container->get(IEventDispatcher::class);
$eventDispatcher->register($config->get(ConfigProvider::EVENTS)->toArray());

/** @var IMigrator $migrator */
$migrator = $container->get(IMigrator::class);
$migrator->runCore();
$migrator->runApps();

/** @var IUserRepositoryService $userRepositoryService */
$userRepositoryService = $container->get(IUserRepositoryService::class);
/** @var IUserService $userService */
$userService = $container->get(IUserService::class);

$userRepositoryService->createSystemUser(
    $userService->getSystemUser()
);
/** @var Legacy $legacy */
$legacy = $container->get(Legacy::class);

$user = new User();
$user->setName("TestUser");
$user->setId(\KST\Config::TEST_USER_ID);
$user->setHash(md5((string) \KST\Config::TEST_USER_ID));
$user->setCreateTs(new DateTime());
$user->setEmail((string) $legacy->getApplication()->get("email"));
$user->setFirstName((string) $legacy->getApplication()->get("name"));
$user->setLastName((string) $legacy->getApplication()->get("name"));
$user->setPhone((string) $legacy->getApplication()->get("phone"));
$user->setWebsite((string) $legacy->getApplication()->get("web"));
$user->setPassword("");
$user->setLocked(true);

$userRepositoryService->createUser($user);
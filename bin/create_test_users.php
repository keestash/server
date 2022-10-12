#!/usr/bin/env php
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
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.If not, see <https://www.gnu.org/licenses/>.
 */

use Keestash\ConfigProvider;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use Keestash\Core\System\Application;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Service\Event\IEventService;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../lib/versioncheck.php';
    require_once __DIR__ . '/../lib/filecheck.php';
    require_once __DIR__ . '/../lib/extensioncheck.php';
    require_once __DIR__ . '/../config/config.php';
    require_once __DIR__ . '/../config/config.php';

    /** @var ContainerInterface $container */
    $container = require_once __DIR__ . '/../lib/start.php';

    /** @var UserRepositoryService $userRepositoryService */
    $userRepositoryService = $container->get(UserRepositoryService::class);

    /** @var Config $config */
    $config = $container->get(Config::class);

    /** @var IEventService $eventManager */
    $eventManager = $container->get(IEventService::class);
    $eventManager->registerAll($config->get(ConfigProvider::EVENTS)->toArray());

    $userAmount = 5;

    for ($i = 0; $i < $userAmount; $i++) {
        $user = createUser(
                $container->get(Application::class)
        );
        $userRepositoryService->createUser($user);
    }
})();

function createUser(Application $legacy): IUser {
    $user = new User();
    $user->setName(hash("md5", uniqid("", true)));
    $user->setHash(
            hash("md5", uniqid("", true))
    );
    $user->setCreateTs(new DateTime());
    $user->setEmail((string) $legacy->getMetaData()->get("email"));
    $user->setFirstName((string) $legacy->getMetaData()->get("name"));
    $user->setLastName((string) $legacy->getMetaData()->get("name"));
    $user->setPhone((string) $legacy->getMetaData()->get("phone"));
    $user->setWebsite((string) $legacy->getMetaData()->get("web"));
    $user->setPassword(
            hash("md5", uniqid())
    );
    return $user;
}
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

namespace KSA\Users\Factory\BackgroundJob;

use Keestash\Core\Service\User\UserService;
use KSA\Users\BackgroundJob\UserDeleteTask;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Config\IConfigService;
use Psr\Container\ContainerInterface;

class UserDeleteTaskFactory {

    public function __invoke(ContainerInterface $container): UserDeleteTask {
        return new UserDeleteTask(
            $container->get(UserService::class)
            , $container->get(IConfigService::class)
            , $container->get(IUserStateRepository::class)
            , $container->get(ILogger::class)
            , $container->get(IEventManager::class)
        );
    }

}
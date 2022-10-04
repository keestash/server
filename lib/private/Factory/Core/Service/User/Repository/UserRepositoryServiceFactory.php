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

namespace Keestash\Factory\Core\Service\User\Repository;

use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\User\Repository\UserRepositoryService;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\Logger\ILogger;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;

class UserRepositoryServiceFactory {

    public function __invoke(ContainerInterface $container): IUserRepositoryService {
        return new UserRepositoryService(
            $container->get(IApiLogRepository::class)
            , $container->get(IFileRepository::class)
            , $container->get(IUserKeyRepository::class)
            , $container->get(IUserRepository::class)
            , $container->get(IUserStateRepository::class)
            , $container->get(FileService::class)
            , $container->get(ILogger::class)
            , $container->get(IEventService::class)
        );
    }

}
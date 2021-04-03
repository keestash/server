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

namespace Keestash\Factory\Core\Service\User;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Repository\Instance\InstanceRepository;
use Keestash\Core\Service\Encryption\Credential\CredentialService;
use Keestash\Core\Service\Encryption\Key\KeyService;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\ApiLog\IApiLogRepository;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserService;
use Psr\Container\ContainerInterface;

class UserServiceFactory {

    public function __invoke(ContainerInterface $container): IUserService {
        return new UserService(
            $container->get(IApiLogRepository::class),
            $container->get(IFileRepository::class),
            $container->get(IUserKeyRepository::class),
            $container->get(IUserRepository::class),
            $container->get(KeyService::class),
            $container->get(Legacy::class),
            $container->get(IUserStateRepository::class),
            $container->get(FileService::class),
            $container->get(InstanceRepository::class),
            $container->get(CredentialService::class),
            $container->get(IDateTimeService::class),
            $container->get(ILogger::class),
            $container->get(IEventManager::class)
        );
    }

}
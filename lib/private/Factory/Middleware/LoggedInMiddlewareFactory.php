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

namespace Keestash\Factory\Middleware;

use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Middleware\LoggedInMiddleware;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\HTTP\IPersistenceService;
use Laminas\Config\Config;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class LoggedInMiddlewareFactory {

    public function __invoke(ContainerInterface $container): LoggedInMiddleware {
        return new LoggedInMiddleware(
            $container->get(IPersistenceService::class)
            , $container->get(InstallerService::class)
            , $container->get(ILogger::class)
            , $container->get(Config::class)
            , $container->get(RouterInterface::class)
            , $container->get(HTTPService::class)
            , $container->get(IUserRepository::class)
            , $container->get(IEnvironmentService::class)
        );
    }

}
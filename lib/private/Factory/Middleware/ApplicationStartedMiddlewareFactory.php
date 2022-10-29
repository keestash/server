<?php
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

use Interop\Container\ContainerInterface;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Middleware\ApplicationStartedMiddleware;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use Psr\Log\LoggerInterface;
use KSP\Core\Service\Router\IRouterService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Http\Server\MiddlewareInterface;

class ApplicationStartedMiddlewareFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): MiddlewareInterface {
        return new ApplicationStartedMiddleware(
            $container->get(IRouterService::class)
            , $container->get(IEnvironmentService::class)
            , $container->get(InstallerService::class)
            , $container->get(IConfigService::class)
            , $container->get(LoggerInterface::class)
        );
    }

}
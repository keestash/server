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

namespace KSA\Login\Factory\Controller;

use Keestash\Core\Manager\SessionManager\SessionManager;
use KSA\Login\Controller\Logout;
use KSP\App\ILoader;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IPersistenceService;
use KSP\Core\Service\Router\IRouterService;
use Mezzio\Router\RouterInterface;
use Psr\Container\ContainerInterface;

class LogoutFactory {

    public function __invoke(ContainerInterface $container): Logout {
        return new Logout(
            $container->get(ITokenRepository::class)
            , $container->get(SessionManager::class)
            , $container->get(IPersistenceService::class)
            , $container->get(IUserRepository::class)
            , $container->get(IRouterService::class)
            , $container->get(ILoader::class)
            , $container->get(RouterInterface::class)
        );
    }

}
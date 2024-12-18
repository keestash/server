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

namespace KST\Service\Factory\Service;

use Keestash\Core\System\Application;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KST\Service\Service\UserService;
use Psr\Container\ContainerInterface;

class UserServiceFactory {

    public function __invoke(ContainerInterface $container): UserService {
        return new UserService(
            $container->get(Application::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(IUserService::class)
            , $container->get(ILocaleService::class)
            , $container->get(ILanguageService::class)
            , $container->get(IEventService::class)
            , $container->get(IUserStateService::class)
            , $container->get(IKeyService::class)
        );
    }

}

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

namespace KSA\Settings\Factory\Api\User;

use doganoo\SimpleRBAC\Service\RBACServiceInterface;
use Keestash\Core\Service\User\UserService;
use KSA\Settings\Api\User\UserEdit;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class UserEditFactory {

    public function __invoke(ContainerInterface $container): UserEdit {
        return new UserEdit(
            $container->get(IUserRepository::class)
            , $container->get(UserService::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(IJWTService::class)
            , $container->get(LoggerInterface::class)
            , $container->get(RBACServiceInterface::class)
            , $container->get(IResponseService::class)
            , $container->get(IUserKeyRepository::class)
        );
    }

}

<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KSA\Settings\Factory\Command;

use Interop\Container\ContainerInterface;
use KSA\Settings\Command\UpdatePassword;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class UpdatePasswordFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): UpdatePassword {
        return new UpdatePassword(
            $container->get(IUserRepository::class)
            , $container->get(IUserService::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(IUserKeyRepository::class)
        );
    }

}

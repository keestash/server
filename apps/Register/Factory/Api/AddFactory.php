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

namespace KSA\Register\Factory\Api;

use doganoo\DIP\Object\String\StringService;
use Keestash\Core\Service\User\UserService;
use KSA\Register\Api\User\Add;
use KSP\Core\Service\App\ILoaderService;
use Psr\Log\LoggerInterface as ILogger;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;

class AddFactory {

    public function __invoke(ContainerInterface $container): Add {
        return new Add(
            $container->get(UserService::class)
            , $container->get(ILoaderService::class)
            , $container->get(ILogger::class)
            , $container->get(IUserRepositoryService::class)
            , $container->get(StringService::class)
        );
    }

}
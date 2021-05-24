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

namespace KSA\PasswordManager\Factory\Repository;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Repository\User\UserRepository;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\Service\HTTP\IJWTService;
use Psr\Container\ContainerInterface;

class CommentRepositoryFactory {

    public function __invoke(ContainerInterface $container): CommentRepository {
        return new CommentRepository(
            $container->get(IBackend::class)
            , $container->get(NodeRepository::class)
            , $container->get(UserRepository::class)
            , $container->get(IDateTimeService::class)
            , $container->get(IJWTService::class)
        );
    }

}
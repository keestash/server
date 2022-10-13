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

namespace KSA\PasswordManager\Factory\Api\Node;

use KSA\PasswordManager\Api\Node\Get;
use KSA\PasswordManager\Repository\CommentRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Repository\Node\PwnedBreachesRepository;
use KSA\PasswordManager\Repository\Node\PwnedPasswordsRepository;
use KSA\PasswordManager\Service\Node\BreadCrumb\BreadCrumbService;
use KSA\PasswordManager\Service\NodeEncryptionService;
use Psr\Log\LoggerInterface as ILogger;
use Psr\Container\ContainerInterface;

class GetFactory {

    public function __invoke(ContainerInterface $container): Get {
        return new Get(
            $container->get(NodeRepository::class)
            , $container->get(BreadCrumbService::class)
            , $container->get(ILogger::class)
            , $container->get(NodeEncryptionService::class)
            , $container->get(CommentRepository::class)
            , $container->get(PwnedPasswordsRepository::class)
            , $container->get(PwnedBreachesRepository::class)
        );
    }

}
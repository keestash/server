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

namespace KSA\PasswordManager\Factory\Repository\Node;

use doganoo\DI\DateTime\IDateTimeService;
use KSA\PasswordManager\Repository\Node\FileRepository;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\ILogger\ILogger;
use Psr\Container\ContainerInterface;

class FileRepositoryFactory {

    public function __invoke(ContainerInterface $container): FileRepository {
        return new FileRepository(
            $container->get(IBackend::class)
            , $container->get(NodeRepository::class)
            , $container->get(IDateTimeService::class)
            , $container->get(\Keestash\Core\Repository\File\FileRepository::class)
            , $container->get(ILogger::class)
        );
    }

}
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

namespace KSA\InstallInstance\Factory\Api\EndUpdate;

use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\Instance\InstallerService;
use Keestash\Core\Service\User\UserService;
use Keestash\Core\System\Installation\Instance\LockHandler;
use KSA\InstallInstance\Api\EndUpdate\EndUpdate;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\App\ILoaderService;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Container\ContainerInterface;

class EndUpdateFactory {

    public function __invoke(ContainerInterface $container): EndUpdate {
        return new EndUpdate(
            $container->get(IL10N::class)
            , $container->get(InstallerService::class)
            , $container->get(LockHandler::class)
            , $container->get(IFileRepository::class)
            , $container->get(FileService::class)
            , $container->get(UserService::class)
            , $container->get(HTTPService::class)
            , $container->get(ILoaderService::class)
            , $container->get(IUserRepositoryService::class)
        );
    }

}
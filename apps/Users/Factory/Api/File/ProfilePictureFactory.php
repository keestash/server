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

namespace KSA\Users\Factory\Api\File;

use Keestash\Core\Manager\FileManager\FileManager;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSA\Users\Api\File\ProfilePicture;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Container\ContainerInterface;

class ProfilePictureFactory {

    public function __invoke(ContainerInterface $container): ProfilePicture {
        return new ProfilePicture(
            $container->get(IUserRepository::class)
            , $container->get(FileService::class)
            , $container->get(RawFileService::class)
            , $container->get(FileManager::class)
        );
    }

}
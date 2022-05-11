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

namespace KSA\Settings\Factory\Api\User;

use Keestash\Core\Service\File\FileService;
use KSA\Settings\Api\User\UpdateProfileImage;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\Upload\IFileService;
use KSP\Core\Service\HTTP\IJWTService;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class UpdateProfileImageFactory implements FactoryInterface {

    public function __invoke(ContainerInterface $container, $requestedName, ?array $options = null) {
        return new UpdateProfileImage(
            $container->get(Config::class)
            , $container->get(IFileService::class)
            , $container->get(IFileRepository::class)
            , $container->get(IJWTService::class)
            , $container->get(FileService::class)
        );
    }

}
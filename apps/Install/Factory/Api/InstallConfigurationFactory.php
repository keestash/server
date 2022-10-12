<?php
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

namespace KSA\Install\Factory\Api;

use Interop\Container\ContainerInterface;
use KSA\Install\Api\InstallConfiguration;
use KSP\Core\Repository\AppRepository\IAppRepository;
use KSP\Core\Service\App\IAppService;
use KSP\Core\Service\App\ILoaderService;
use Laminas\ServiceManager\Factory\FactoryInterface;

class InstallConfigurationFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): InstallConfiguration {
        return new InstallConfiguration(
            $container->get(ILoaderService::class)
            , $container->get(IAppRepository::class)
            , $container->get(IAppService::class)
        );
    }

}
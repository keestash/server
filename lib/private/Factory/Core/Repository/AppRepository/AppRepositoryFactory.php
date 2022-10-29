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

namespace Keestash\Factory\Core\Repository\AppRepository;


use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Repository\AppRepository\AppRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\Repository\AppRepository\IAppRepository;
use Psr\Log\LoggerInterface;
use Psr\Container\ContainerInterface;

class AppRepositoryFactory {

    public function __invoke(ContainerInterface $container): IAppRepository {
        return new AppRepository(
            $container->get(IBackend::class)
            , $container->get(IDateTimeService::class)
            , $container->get(LoggerInterface::class)
        );
    }

}
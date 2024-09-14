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

namespace Keestash\Factory\Core\Service\Queue;

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Core\Service\Queue\QueueService;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Encryption\IBase64Service;
use KSP\Core\Service\Queue\IQueueService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class QueueServiceFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): IQueueService {
        return new QueueService(
            $container->get(IQueueRepository::class)
            , $container->get(IDateTimeService::class)
            , $container->get(IBase64Service::class)
            , $container->get(LoggerInterface::class)
        );
    }

}
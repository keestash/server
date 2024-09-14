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

namespace Keestash\Factory\Command\Worker;

use Keestash\Command\KeestashCommand;
use Keestash\Command\Worker\WorkerRunner;
use Keestash\Core\Builder\Data\DataServiceBuilder;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Metric\ICollectorService;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Queue\Handler\IEventHandler;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class WorkerRunnerFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container,
                           $requestedName,
        ?array             $options = null
    ): KeestashCommand {
        $dataService = (new DataServiceBuilder())
            ->withAppId('core/system')
            ->withContext('queue-worker')
            ->withConfig($container->get(Config::class))
            ->build();

        return new WorkerRunner(
            $container->get(IQueueService::class)
            , $container->get(LoggerInterface::class)
            , $container->build(IQueueRepository::class)
            , $container->get(IEventHandler::class)
            , $dataService
            , $container->get(ICollectorService::class)
        );
    }

}

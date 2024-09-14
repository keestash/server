<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace Keestash\Factory\Core\Service\Metric;

use Keestash\Core\Service\Metric\CollectorService;
use KSP\Core\Service\Config\IConfigService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Prometheus\RegistryInterface;
use Psr\Container\ContainerInterface;

class CollectorFactory implements FactoryInterface {

    #[\Override]
    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): CollectorService {
        /** @var IConfigService $config */
        $config = $container->get(IConfigService::class);

        return new CollectorService(
            $container->get(RegistryInterface::class),
            $config->getValue('prometheus_namespace', ''),
            $config->getValue('prometheus_prefix', '')
        );
    }

}

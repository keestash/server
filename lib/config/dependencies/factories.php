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

use Keestash\Api\PingHandler;
use Keestash\Factory\Api\PingHandlerFactory;
use Keestash\Factory\Core\Service\Metric\RegistryFactory;
use Prometheus\CollectorRegistry;

return
    array_merge(
        require __DIR__ . '/factories/repositories.php'
        , require __DIR__ . '/factories/events.php'
        , require __DIR__ . '/factories/command.php'
        , require __DIR__ . '/factories/service.php'
        , require __DIR__ . '/factories/middleware.php'
        , [
            PingHandler::class       => PingHandlerFactory::class,
            CollectorRegistry::class => RegistryFactory::class
        ]
    );

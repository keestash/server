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

namespace Keestash\Factory\Core\System\RateLimit;

use Keestash\Core\Builder\Data\DataServiceBuilder;
use Keestash\Core\System\RateLimit\FileRateLimiter;
use KSP\Core\Service\Logger\ILogger;
use Laminas\Config\Config;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use RateLimit\Rate;
use RateLimit\RateLimiter;

class FileRateLimiterFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): RateLimiter {
        $dataManager = (new DataServiceBuilder())
            ->withAppId('core/system')
            ->withContext('ratelimiter')
            ->withConfig($container->get(Config::class))
            ->build();

        return new FileRateLimiter(
            Rate::perSecond(2)
            , $container->get(ILogger::class)
            , $dataManager
        );
    }

}
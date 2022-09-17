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

namespace KSA\PasswordManager\Factory\Service\Node;

use GuzzleHttp\ClientInterface;
use KSA\PasswordManager\Service\Node\PwnedService;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\CSV\ICSVService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;

class PwnedServiceFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): PwnedService {
        return new PwnedService(
            $container->get(ICSVService::class)
            , $container->get(ClientInterface::class)
            , $container->get(IConfigService::class)
            , $container->get(ILogger::class)
        );
    }

}
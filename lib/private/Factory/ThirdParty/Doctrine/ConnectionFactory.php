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

namespace Keestash\Factory\ThirdParty\Doctrine;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Logging\Middleware;
use KSP\Core\Service\Config\IConfigService;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ConnectionFactory implements FactoryInterface {

    public function __invoke(
        ContainerInterface $container
        ,                  $requestedName
        , ?array           $options = null
    ): Connection {
        /** @var IConfigService $config */
        $config = $container->get(IConfigService::class);
        /** @var LoggerInterface $logger */
        $logger = $container->get(LoggerInterface::class);
        // $logger = new NullLogger();
        return DriverManager::getConnection(
            [
                'driver'     => 'pdo_mysql'
                , 'host'     => (string) $config->getValue('db_host')
                , 'dbname'   => (string) $config->getValue('db_name')
                , 'port'     => (int) $config->getValue('db_port')
                , 'user'     => (string) $config->getValue('db_user')
                , 'password' => (string) $config->getValue('db_password')
            ],
            (new Configuration())->setMiddlewares(
                [new Middleware($logger)]
            )
        );
    }

}
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

namespace KST\Service\Factory\ThirdParty\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Keestash\ConfigProvider;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

class ConnectionFactory {

    public function __invoke(ContainerInterface $container): Connection {
        /** @var Config $config */
        $config   = $container->get(Config::class);
        $fileName = $config->get(ConfigProvider::TEST_PATH) . '/config/test.unit.keestash.sqlite';

        return DriverManager::getConnection(
            [
                'driver' => 'pdo_sqlite'
                , 'path' => $fileName
            ]
        );
    }

}
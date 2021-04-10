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

use Keestash\ConfigProvider;
use Laminas\ConfigAggregator\ConfigAggregator;

return [
    ConfigAggregator::CACHE_FILEMODE         => 777
    , ConfigAggregator::ENABLE_CACHE         => false
    , ConfigProvider::INSTANCE_DB_PATH       => __DIR__ . '/../../config/.instance.sqlite'
    , ConfigProvider::CONFIG_PATH            => realpath(__DIR__ . '/../../config/')
    , ConfigProvider::ASSET_PATH             => realpath(__DIR__ . '/../../asset/')
    , ConfigProvider::IMAGE_PATH             => realpath(__DIR__ . '/../../data/image/')
    , ConfigProvider::PHINX_PATH             => realpath(__DIR__ . '/../../config/phinx/')
    , ConfigProvider::DATA_PATH              => realpath(__DIR__ . '/../../data/')
    , ConfigProvider::INSTANCE_PATH          => realpath(__DIR__ . '/../../')
    , ConfigProvider::APP_PATH               => realpath(__DIR__ . '/../../apps/')
    , ConfigProvider::INSTALL_INSTANCE_ROUTE => 'install_instance'
    , 'dependencies'                         => require __DIR__ . '/dependencies.php'
    , ConfigProvider::API_ROUTER             => require __DIR__ . '/router.php'
    , 'templates'                            => [
        'extension' => 'twig'
        , 'paths'   => [
            'root'    => [realpath(__DIR__ . '/../../template/app/')]
            , 'email' => [realpath(__DIR__ . '/../../template/email/')]
        ]
    ]
];
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

use Laminas\Config\Config;
use Laminas\ConfigAggregator\ConfigAggregator;
use Laminas\ServiceManager\ServiceManager;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../lib/versioncheck.php';
require __DIR__ . '/../lib/filecheck.php';
require __DIR__ . '/../lib/extensioncheck.php';

$configAggregator = new ConfigAggregator(
    require __DIR__ . '/config/provider.php'
    , __DIR__ . '/../config/cache/config-cache.php'
);
$config       = $configAggregator->getMergedConfig();
$configObject = new Config($config);

$dependencies                       = $config['dependencies'];
$dependencies['services']['config'] = $config;

$serviceManager = new ServiceManager(
    $dependencies
);

unset($config['dependencies']);

$serviceManager->setAllowOverride(true);
$serviceManager->setFactory(
    Config::class, fn() => $configObject
);
$serviceManager->setAllowOverride(false);

return $serviceManager;
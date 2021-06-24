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

use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . '/../../vendor/autoload.php';

/** @var ServiceManager $container */
$container         = require __DIR__ . '/../../lib/start.php';
$mocked            = require __DIR__ . '/../config/dependencies.php';
$mockedApps        = glob(__DIR__ . '/../../apps/*/Test/config/dependencies.php');
$mockedAliases     = require __DIR__ . '/../config/aliases.php';
$mockedAliasesApps = glob(__DIR__ . '/../../apps/*/Test/config/aliases.php');

$container->setAllowOverride(true);
foreach ($mocked as $name => $factory) {
    $container->setFactory($name, $factory);
}

foreach ($mockedApps as $path) {

    if (false === is_file($path)) {
        continue;
    }

    $mocked = require realpath($path);

    foreach ($mocked as $name => $factory) {
        $container->setFactory($name, $factory);
    }
}

foreach ($mockedAliases as $alias => $name) {
    $container->setAlias($alias, $name);
}

foreach ($mockedAliasesApps as $path) {

    if (false === is_file($path)) {
        continue;
    }

    $mocked = require realpath($path);

    foreach ($mocked as $name => $factory) {
        $container->setFactory($name, $factory);
    }
}

$container->setAllowOverride(false);
return $container;

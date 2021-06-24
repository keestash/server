#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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
use Keestash\Core\Service\Stylesheet\Compiler;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;

$action = $argv[1] ?? 'add';

(function () use ($action) {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../vendor/autoload.php';
    /** @var ContainerInterface $container */
    $container = require_once __DIR__ . '/../lib/start.php';

    /** @var Config $config */
    $config = $container->get(Config::class);
    /** @var Compiler $compiler */
    $compiler = $container->get(Compiler::class);
    $files    = glob(
            $config->get(ConfigProvider::INSTANCE_PATH) . '/apps/*/scss/*.scss'
    );

    foreach ($files as $file) {

        $pathInfo    = pathinfo($file);
        $destination = realpath($config->get(ConfigProvider::INSTANCE_PATH) . '/public/css/') . '/' . $pathInfo['filename'] . '.css';

        echo 'compiling ' . $file . ' at destination ' . $destination . PHP_EOL;
        $compiler->compile(
                $file
                , $destination
        );

    }


})();

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

use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Event\IEventService;
use Laminas\Config\Config;
use Psr\Container\ContainerInterface;
use Symfony\Component\Console\Application;

(function () {

    chdir(dirname(__DIR__));

    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../lib/versioncheck.php';
    require_once __DIR__ . '/../lib/filecheck.php';
    require_once __DIR__ . '/../lib/extensioncheck.php';
    require_once __DIR__ . '/../config/config.php';

    /** @var ContainerInterface $container */
    $container = require_once __DIR__ . '/../lib/start.php';
    /** @var Config $config */
    $config = $container->get(Config::class);
    /** @var Application $legacy */
    $legacy = $container->get(Application::class);

    /** @var IEnvironmentService $environmentService */
    $environmentService = $container->get(IEnvironmentService::class);
    $environmentService->setEnv(ConfigProvider::ENVIRONMENT_CONSOLE);
    $cliVersion  = "1.0.0";
    $application = new Application(
            $legacy->getMetaData()->get("name") . " CLI Tools"
            , $cliVersion
    );

    /** @var IEventService $eventManager */
    $eventManager = $container->get(IEventService::class);
    $eventManager->registerAll($config->get(ConfigProvider::EVENTS)->toArray());

    foreach ($config->get(ConfigProvider::COMMANDS)->toArray() as $commandClass) {

        /** @var KeestashCommand $command */
        $command = $container->get($commandClass);
        $application->add($command);
    }

    $application->run();

})();

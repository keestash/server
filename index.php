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

// we want to keep the global namespace clean.
// Therefore, we call our framework within an
// anonymous function.
use Keestash\ConfigProvider;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Event\IEventDispatcher;
use Laminas\Config\Config;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

(function () {
    chdir(dirname(__DIR__));

    /** @var ContainerInterface $container */
    $container = require_once __DIR__ . '/lib/start.php';
    /** @var Config $config */
    $config = $container->get(Config::class);
    /** @var Application $app */
    $app = $container->get(Application::class);
    /** @var IEnvironmentService $environmentService */
    $environmentService = $container->get(IEnvironmentService::class);
    $environmentService->setEnv(ConfigProvider::ENVIRONMENT_WEB);

    (require_once __DIR__ . '/config/config/pipeline.php')($app);

    $router = $config->get(ConfigProvider::WEB_ROUTER);

    /** @var Config $route */
    foreach ($router[ConfigProvider::ROUTES] as $route) {
        $middleware = $route->get('middleware');
        $name       = $route->get('name');
        $path       = $route->get('path');

        $app->get(
            $path
            , $middleware
            , $name
        );
    }

    /** @var IEventDispatcher $eventDispatcher */
    $eventDispatcher = $container->get(IEventDispatcher::class);
    $eventDispatcher->register($config->get(ConfigProvider::EVENTS)->toArray());
    $app->run();
    
})();

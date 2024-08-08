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
use Keestash\Core\DTO\Event\ApplicationStartedEvent;
use KSP\Api\IVerb;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Event\IEventService;
use Laminas\Config\Config;
use Mezzio\Application;
use Psr\Container\ContainerInterface;

(static function (): void {
    chdir(dirname(__DIR__));

    set_time_limit(0);

    /** @var ContainerInterface $container */
    $container = require __DIR__ . '/../lib/start.php';
    /** @var Config $config */
    $config = $container->get(Config::class);
    /** @var Application $app */
    $app = $container->get(Application::class);
    /** @var IEnvironmentService $environmentService */
    $environmentService = $container->get(IEnvironmentService::class);
    $environmentService->setEnv(ConfigProvider::ENVIRONMENT_API);

    (require __DIR__ . '/../lib/config/pipeline/api/pipeline.php')($app);

    $router = $config->get(ConfigProvider::API_ROUTER);

    /** @var IEventService $eventService */
    $eventService = $container->get(IEventService::class);
    $eventService->registerAll($config->get(ConfigProvider::EVENTS)->toArray());
    $eventService->execute(new ApplicationStartedEvent(new DateTime()));

    /** @var Config $route */
    foreach ($router[ConfigProvider::ROUTES] as $route) {
        $method     = strtolower((string) $route->get('method'));
        $middleware = $route->get('middleware');
        $name       = $route->get('name');
        $path       = $route->get('path');

        if ($middleware instanceof Config) {
            $middleware = $middleware->toArray();
        }

        switch ($method) {
            case IVerb::GET:
                $app->get(
                    $path
                    , $middleware
                    , $name
                );
                break;
            case IVerb::POST:
                $app->post(
                    $path
                    , $middleware
                    , $name
                );
                break;
            case IVerb::PUT:
                $app->put(
                    $path
                    , $middleware
                    , $name
                );
                break;
            case IVerb::DELETE:
                $app->delete(
                    $path
                    , $middleware
                    , $name
                );
                break;
            default:
                throw new Exception('unknown method ' . $method);
        }
    }
    $app->run();
})();

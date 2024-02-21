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

namespace Keestash\Factory\Core\Logger;

use Keestash\ConfigProvider;
use Keestash\Core\Builder\Logger\LoggerBuilder;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\System\Application;
use KSP\Core\Service\Config\IConfigService;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use Laminas\Config\Config;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class LoggerFactory {

    public function __invoke(ContainerInterface $container): LoggerInterface {
        /** @var IConfigService $configService */
        $configService = $container->get(IConfigService::class);
        /** @var IEnvironmentService $environmentService */
        $environmentService = $container->get(IEnvironmentService::class);
        /** @var Application $application */
        $application = $container->get(Application::class);
        /** @var Config $config */
        $config = $container->get(Config::class);
        /** @var InstanceDB $instanceDb */
        $instanceDb = $container->get(InstanceDB::class);

        $isUnitTest   = $environmentService->isUnitTest();
        $nameInternal = $application->getMetaData()->get('name_internal');
        $logFileName  = true === $isUnitTest
            ? $nameInternal . '_test.log'
            : $nameInternal . '.log';

        $logLevel       = (int) $configService->getValue("log_level", Logger::ERROR);
        $sentryLogLevel = $instanceDb->getOption('sentry_log_level');
        $dataRoot       = (string) $config->get(ConfigProvider::DATA_PATH);

        return (new LoggerBuilder())
            ->withLogLevel(
                (int) $configService->getValue("log_level", Logger::ERROR)
            )
            ->withPath(
                $dataRoot . '/' . $logFileName
            )
            ->withFormatter(new JsonFormatter())
            ->withStreamHandler()
            ->withDevHandler(
                $configService->getValue('sentry_dsn', ''),
                null !== $sentryLogLevel
                    ? (int) $sentryLogLevel
                    : $logLevel
            )
            ->build();

    }


}

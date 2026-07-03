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
use Keestash\Config\Config;
use Monolog\Formatter\JsonFormatter;
use Monolog\Level;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

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
        $isConsole    = $environmentService->isConsole();
        $nameInternal = $application->getMetaData()->get('name_internal');
        $logFileName  = $this->getFileName($isUnitTest, $isConsole, $nameInternal);

        $logLevel       = $this->resolveLogLevel((string) $configService->getValue("log_level", LogLevel::DEBUG));
        $sentryLogLevel = $instanceDb->getOption('sentry_log_level');
        $dataRoot       = (string) $config->get(ConfigProvider::DATA_PATH);

        return (new LoggerBuilder())
            ->withLogLevel($logLevel->value)
            ->withPath(
                $dataRoot . '/' . $logFileName
            )
            ->withFormatter(new JsonFormatter())
            ->withHandler()
            ->withDevHandler(
                $configService->getValue('sentry_dsn', ''),
                null !== $sentryLogLevel
                    ? $this->resolveLogLevel((string) $sentryLogLevel)->value
                    : $logLevel->value
            )
            ->build();

    }

    private function resolveLogLevel(string $raw): Level {
        if (is_numeric($raw) && (int) $raw > 0) {
            try {
                return Level::from((int) $raw);
            } catch (\ValueError) {
                // fall through to name resolution
            }
        }
        try {
            /** @phpstan-ignore argument.type */
            return Level::fromName($raw);
        } catch (\ValueError) {
            return Level::Error;
        }
    }

    private function getFileName(bool $isUnitTest, bool $isConsole, string $nameInternal): string {
        $suffix = '';

        if (true === $isUnitTest) {
            $suffix = '_test';
        }

        if (true === $isConsole) {
            $suffix = '_console';
        }
        return "$nameInternal$suffix.log";
    }

}

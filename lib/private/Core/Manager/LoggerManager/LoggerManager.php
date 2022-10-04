<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace Keestash\Core\Manager\LoggerManager;

use Keestash;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\Logger\Logger;
use Keestash\Legacy\Legacy;
use KSP\Core\Manager\LoggerManager\ILoggerManager;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Logger\ILogger;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;
use Sentry\Monolog\Handler;
use Sentry\SentrySdk;
use function Sentry\init;

/**
 * Class LoggerManager
 * @package Keestash\Core\Manager\LoggerManager
 */
class LoggerManager implements ILoggerManager {

    private ConfigService       $configService;
    private Legacy              $legacy;
    private IEnvironmentService $environmentService;

    public function __construct(
        ConfigService         $configService
        , Legacy              $legacy
        , IEnvironmentService $environmentService
    ) {
        $this->configService      = $configService;
        $this->legacy             = $legacy;
        $this->environmentService = $environmentService;
    }

    private function getLogfilePath(): string {
        $name = $this->legacy->getApplication()->get("name_internal");
        return realpath(__DIR__ . '/../../../../../data/') . "/$name.log";
    }

    public function getLogger(): ILogger {
        $logLevel      = $this->configService->getValue("log_level", \Monolog\Logger::ERROR);
        $jsonFormatter = new JsonFormatter();
        $logger        = new Logger(ILoggerManager::FILE_LOGGER);
        $debug         = (bool) $this->configService->getValue("debug", false);
        $streamHandler = new StreamHandler(
            $this->getLogfilePath()
            , $logLevel
        );
        $streamHandler->setFormatter($jsonFormatter);
        $logger->pushHandler($streamHandler);
        $logger = $this->addSentryHandler(
            $logger
            , $jsonFormatter
            , (int) $logLevel
            , $debug
        );
        return $this->addDevHandler($logger, $debug);
    }

    private function addSentryHandler(
        ILogger              $logger
        , FormatterInterface $formatter
        , int                $logLevel
        , bool               $debug
    ): ILogger {
        $sentryDsn = $this->configService->getValue('sentry_dsn');
        $isTest    = $this->environmentService->isUnitTest();

        if (null === $sentryDsn) {
            return $logger;
        }

        if (true === $debug || true === $isTest) {
            return $logger;
        }

        init(['dsn' => $sentryDsn]);
        $sentryHandler = new Handler(
            SentrySdk::getCurrentHub()
            , $logLevel
            , true
            , true
        );
        $sentryHandler->setFormatter($formatter);
        $sentryHandler->setLevel($logLevel);

        $logger->pushHandler($sentryHandler);
        return $logger;
    }

    private function addDevHandler(ILogger $logger, bool $debug): ILogger {
        if (false === $debug) return $logger;
        $htmlStreamHandler = new StreamHandler(
            $this->getLogfilePath() . ".html"
            , $this->configService->getValue("log_level", \Monolog\Logger::DEBUG)
        );
        $htmlStreamHandler->setFormatter(
            new HtmlFormatter()
        );
        $logger->pushHandler($htmlStreamHandler);
        return $logger;
    }

}
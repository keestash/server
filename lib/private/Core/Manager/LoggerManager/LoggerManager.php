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
use Keestash\Core\Logger\Logger;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Legacy\Legacy;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\LoggerManager\ILoggerManager;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Handler\StreamHandler;

/**
 * Class LoggerManager
 * @package Keestash\Core\Manager\LoggerManager
 */
class LoggerManager implements ILoggerManager {

    private ConfigService $configService;
    private Legacy        $legacy;

    public function __construct(
        ConfigService $configService
        , Legacy $legacy
    ) {
        $this->configService = $configService;
        $this->legacy        = $legacy;
    }

    private function getLogfilePath(): string {
        $name = $this->legacy->getApplication()->get("name_internal");
        return realpath(__DIR__ . '/../../../../../data/') . "/$name.log";
    }

    public function getFileLogger(): ILogger {
        $logger        = new Logger(ILoggerManager::FILE_LOGGER);
        $streamHandler = new StreamHandler(
            $this->getLogfilePath()
            , $this->configService->getValue("log_level", \Monolog\Logger::DEBUG)
        );
        $streamHandler->setFormatter(
            new JsonFormatter()
        );
        $logger->pushHandler($streamHandler);

        $logger = $this->addDevHandler($logger);
        return $logger;
    }

    private function addDevHandler(Logger $logger): Logger {
        if (false === $this->configService->getValue("debug", false)) return $logger;
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
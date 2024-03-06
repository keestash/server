<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace Keestash\Core\Builder\Logger;

use KSP\Core\Builder\Logger\ILoggerBuilder;
use KSP\Core\Manager\LoggerManager\ILoggerManager;
use Monolog\Formatter\FormatterInterface;
use Monolog\Handler\HandlerInterface;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryUsageProcessor;
use Monolog\Processor\ProcessIdProcessor;
use Monolog\Processor\UidProcessor;
use Monolog\Processor\WebProcessor;
use Psr\Log\LoggerInterface;
use Sentry\Monolog\Handler;
use Sentry\SentrySdk;
use function Sentry\init;

class LoggerBuilder implements ILoggerBuilder {

    private int                $logLevel = Logger::ERROR;
    private FormatterInterface $formatter;
    private array              $handlers;
    private string             $path;

    public function withLogLevel(int $logLevel): ILoggerBuilder {
        $instance           = clone $this;
        $instance->logLevel = $logLevel;
        return $instance;
    }

    public function withPath(string $path): ILoggerBuilder {
        $instance       = clone $this;
        $instance->path = $path;
        return $instance;
    }

    public function withFormatter(FormatterInterface $formatter): ILoggerBuilder {
        $instance            = clone $this;
        $instance->formatter = $formatter;
        return $instance;
    }

    public function withHandler(): ILoggerBuilder {
        $instance             = clone $this;
        $instance->handlers[] = new RotatingFileHandler(
            filename: $this->path
            , maxFiles: 15
            , level: $this->logLevel
        );
        return $instance;
    }

    public function withDevHandler(string $sentryDsn, int $logLevel = -1): ILoggerBuilder {
        $instance = clone $this;
        if ('' === $sentryDsn) {
            return $instance;
        }

        init(
            [
                'dsn'                    => $sentryDsn
                , 'traces_sample_rate'   => 1
                , 'profiles_sample_rate' => 1.0
            ]
        );

        if ($logLevel === -1) {
            $logLevel = $this->logLevel;
        }

        $instance->handlers[] = new Handler(
            SentrySdk::getCurrentHub()
            , $logLevel
            , true
            , true
        );

        return $instance;
    }

    public function build(): LoggerInterface {
        $logger = new Logger(ILoggerManager::FILE_LOGGER);

        $logger->pushProcessor(new WebProcessor());
        $logger->pushProcessor(new UidProcessor());
        $logger->pushProcessor(new MemoryUsageProcessor());
        $logger->pushProcessor(new ProcessIdProcessor());
        /** @var HandlerInterface $handler */
        foreach ($this->handlers as $handler) {
            $handler->setLevel($this->logLevel);
            $handler->setFormatter($this->formatter);
            $logger->pushHandler($handler);
        }
        return $logger;
    }

}

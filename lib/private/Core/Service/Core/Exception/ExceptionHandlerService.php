<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Core\Service\Core\Exception;

use KSP\Core\Service\Core\Exception\IExceptionHandlerService;
use Psr\Log\LoggerInterface;
use Throwable;

class ExceptionHandlerService implements IExceptionHandlerService {

    public function __construct(
        private readonly LoggerInterface $logger
    ) {
    }

    public function registerHandler(string $requestId): void {
        $this->setErrorHandler($requestId);
        $this->setExceptionHandler($requestId);
    }

    private function setExceptionHandler(string $requestId): void {
        $self = $this;
        set_exception_handler(
            static function (Throwable $exception) use ($self, $requestId): void {

                $self->logger->error(
                    (string) json_encode(
                        [
                            "id"                => $exception->getCode()
                            , 'requestId'       => $requestId
                            , "message"         => $exception->getMessage()
                            , "file"            => $exception->getFile()
                            , "line"            => $exception->getLine()
                            , "trace"           => json_encode($exception->getTrace())
                            , "trace_as_string" => $exception->getTraceAsString()
                        ]
                    )
                );
            });
    }

    private function setErrorHandler(string $requestId): void {
        $self = $this;
        set_error_handler(
        /** @phpstan-ignore-next-line */
            static function (int $id
                , string         $message
                , string         $file
                , int            $line
                , array          $context = []
            ) use ($self, $requestId): void {

                $self->logger->error(
                    (string) json_encode(
                        [
                            "id"                => $id
                            , 'requestId'       => $requestId
                            , "message"         => $message
                            , "file"            => $file
                            , "line"            => $line
                            , "context"         => $context
                            , "debug_backtrace" => debug_backtrace()
                        ]
                    ),
                    $context
                );

            });

    }

}
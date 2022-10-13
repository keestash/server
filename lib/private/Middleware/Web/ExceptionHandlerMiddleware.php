<?php
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

namespace Keestash\Middleware\Web;

use Composer\InstalledVersions;
use KSP\Core\Service\Config\IConfigService;
use Psr\Log\LoggerInterface as ILogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;
use Whoops\Handler\PrettyPageHandler;
use Whoops\Run;

class ExceptionHandlerMiddleware implements MiddlewareInterface {

    private IConfigService $configService;
    private ILogger        $logger;

    public function __construct(
        IConfigService $configService
        , ILogger      $logger
    ) {
        $this->configService = $configService;
        $this->logger        = $logger;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $showErrors = $this->configService->getValue("show_errors", false);

        if (true === $showErrors && in_array('filp/whoops', InstalledVersions::getInstalledPackages(), true)) {
            $whoops = new Run();
            $whoops->pushHandler(new PrettyPageHandler());
            $whoops->register();
            return $handler->handle($request);
        }
        $this->setHandler();
        return $handler->handle($request);
    }

    private function setHandler(): void {
        set_error_handler(
        /** @phpstan-ignore-next-line */
            function (int $id
                , string  $message
                , string  $file
                , int     $line
                , array   $context
            ) {

                $this->logger->error(
                    (string) json_encode(
                        [
                            "id"                => $id
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

        set_exception_handler(
            function (Throwable $exception): void {

                $this->logger->error(
                    (string) json_encode(
                        [
                            "id"                => $exception->getCode()
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

}
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

namespace Keestash\Middleware;

use Keestash\Core\Service\Instance\InstallerService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use SessionHandlerInterface;

class SessionHandlerMiddleware implements MiddlewareInterface {

    private InstallerService        $installerService;
    private SessionHandlerInterface $sessionHandler;

    public function __construct(
        InstallerService $installerService
        , SessionHandlerInterface $sessionHandler
    ) {
        $this->installerService = $installerService;
        $this->sessionHandler   = $sessionHandler;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        if (true === $this->installerService->hasIdAndHash()) {
            session_set_save_handler(
                $this->sessionHandler
            );
        }
        return $handler->handle($request);
    }

}
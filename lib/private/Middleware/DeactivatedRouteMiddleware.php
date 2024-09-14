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

namespace Keestash\Middleware;

use Keestash\Api\Response\NotFoundResponse;
use KSP\Core\Service\Router\IRouterService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class DeactivatedRouteMiddleware implements MiddlewareInterface {

    public function __construct(
        private readonly LoggerInterface  $logger
        , private readonly IRouterService $routerService
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $this->logger->info(
            'deactivated route called'
            , [
                'route'           => $this->routerService->getMatchedPath($request)
                , 'request'       => (array) $request->getParsedBody()
                , 'server_params' => $request->getServerParams()
            ]
        );
        return new NotFoundResponse(['route not found']);
    }

}
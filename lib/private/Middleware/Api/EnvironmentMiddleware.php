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

namespace Keestash\Middleware\Api;

use Keestash\ConfigProvider;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class EnvironmentMiddleware implements MiddlewareInterface {

    private IEnvironmentService $environmentService;

    public function __construct(IEnvironmentService $environmentService) {
        $this->environmentService = $environmentService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $isSaas = $this->environmentService->isSaas();
        $isSaas = true;
        return $handler->handle(
            $request->withAttribute(
                ConfigProvider::ENVIRONMENT_SAAS,
                $isSaas
            )
        );
    }

}
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

use GuzzleHttp\Psr7\Utils;
use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\Router\IRouterService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class SanitizeInputMiddleware implements MiddlewareInterface {

    public function __construct(
        private readonly LoggerInterface  $logger
        , private readonly IRouterService $routerService
    ) {
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $path = $this->routerService->getMatchedPath($request);
        if (in_array($path, [IPaymentService::PAYMENT_WEBHOOK_ENDPOINT], true)) {
            return $handler->handle($request);
        }

        $queryParams    = $request->getQueryParams();
        $newQueryParams = [];
        foreach ($queryParams as $key => $value) {
            $sanitized            = $this->sanitize($value);
            $newQueryParams[$key] = $sanitized;
        }
        $request = $request->withQueryParams($newQueryParams);

        $body    = (string) $request->getBody();
        $body    = $this->sanitize($body);
        $request = $request->withBody(Utils::streamFor($body));

        return $handler->handle($request);
    }

    private function sanitize(string $raw): string {
        return (string) filter_var($raw, FILTER_SANITIZE_SPECIAL_CHARS);
    }

}

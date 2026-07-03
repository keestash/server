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

use KSP\Core\Service\Payment\IPaymentService;
use KSP\Core\Service\Router\IRouterService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class SanitizeInputMiddleware implements MiddlewareInterface {

    private const array SKIP_KEYS = ['password', 'password_repeat', 'secret', 'key', 'token', 'hash'];

    public function __construct(
        private LoggerInterface  $logger
        , private IRouterService $routerService
    ) {
    }

    #[\Override]
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

        $parsedBody = $request->getParsedBody();
        if (is_array($parsedBody)) {
            $request = $request->withParsedBody($this->sanitizeArray($parsedBody));
        }

        return $handler->handle($request);
    }

    private function sanitizeArray(array $data): array {
        $result = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $result[$key] = $this->sanitizeArray($value);
            } elseif (is_string($value) && !in_array($key, self::SKIP_KEYS, true)) {
                $result[$key] = $this->sanitize($value);
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }

    private function sanitize(string $raw): string {
        return (string) filter_var($raw, FILTER_SANITIZE_SPECIAL_CHARS);
    }

}

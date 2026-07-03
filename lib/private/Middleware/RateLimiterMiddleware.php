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

namespace Keestash\Middleware;

use KSP\Api\IResponse;
use KSP\Core\Service\Config\IConfigService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RateLimit\Exception\LimitExceeded;
use RateLimit\RateLimiter;

final readonly class RateLimiterMiddleware implements MiddlewareInterface {

    public function __construct(
        private RateLimiter    $rateLimiter
        , private IConfigService $configService
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $ipAddress = $this->resolveClientIp($request->getServerParams());

        try {
            $this->rateLimiter->limit($ipAddress);
        } catch (LimitExceeded) {
            return new JsonResponse(['error' => 'Too many requests'], IResponse::TOO_MANY_REQUESTS);
        }

        return $handler->handle($request);
    }

    /**
     * Determine the client IP used as the rate-limit key.
     *
     * X-Forwarded-For is client-controlled, so it is only honoured when the
     * immediate peer (REMOTE_ADDR) is a configured trusted proxy. Otherwise the
     * header is ignored and REMOTE_ADDR is used directly, so an attacker cannot
     * bypass the limiter by spoofing the header.
     *
     * @param array<string, mixed> $serverParams
     */
    private function resolveClientIp(array $serverParams): string {
        $remoteAddr = (string) ($serverParams['REMOTE_ADDR'] ?? '127.0.0.1');

        /** @var list<string> $trustedProxies */
        $trustedProxies = (array) $this->configService->getValue('trusted_proxies', []);

        if ([] === $trustedProxies || false === in_array($remoteAddr, $trustedProxies, true)) {
            return $remoteAddr;
        }

        $forwardedFor = (string) ($serverParams['HTTP_X_FORWARDED_FOR'] ?? '');
        if ('' === $forwardedFor) {
            return $remoteAddr;
        }

        // Walk right-to-left and return the first hop that is not itself a
        // trusted proxy — that is the closest client we can attribute.
        $hops = array_map('trim', explode(',', $forwardedFor));
        for ($i = count($hops) - 1; $i >= 0; $i--) {
            if ('' === $hops[$i]) {
                continue;
            }
            if (false === in_array($hops[$i], $trustedProxies, true)) {
                return $hops[$i];
            }
        }

        return $remoteAddr;
    }

}

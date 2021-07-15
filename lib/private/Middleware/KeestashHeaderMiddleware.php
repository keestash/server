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

use Keestash\ConfigProvider;
use Keestash\Core\Service\Router\Verification;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class KeestashHeaderMiddleware implements MiddlewareInterface {

    private Config              $config;
    private Verification        $verification;
    private IEnvironmentService $environmentService;
    private IRouterService      $routerService;

    public function __construct(
        Config $config
        , Verification $verification
        , IEnvironmentService $environmentService
        , IRouterService $routerService
    ) {
        $this->config             = $config;
        $this->verification       = $verification;
        $this->environmentService = $environmentService;
        $this->routerService      = $routerService;
    }

    private function getPublicRoutes(): array {
        if (true === $this->environmentService->isWeb()) {
            return $this->config
                ->get(ConfigProvider::WEB_ROUTER)
                ->get(ConfigProvider::PUBLIC_ROUTES)
                ->toArray();
        }

        if (true === $this->environmentService->isApi()) {
            return $this->config
                ->get(ConfigProvider::API_ROUTER)
                ->get(ConfigProvider::PUBLIC_ROUTES)
                ->toArray();
        }
        return [];
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        if (true === $this->environmentService->isWeb()) {
            return $handler->handle($request);
        }

        $publicRoutes = $this->getPublicRoutes();
        $currentPath  = $this->routerService->getMatchedPath($request);

        foreach ($publicRoutes as $publicRoute) {
            if ($currentPath === $publicRoute) {
                return $handler->handle($request);
            }
        }

        $token = $this->verification->verifyToken(
            [
                Verification::FIELD_NAME_TOKEN       => $request->getHeader(Verification::FIELD_NAME_TOKEN)[0] ?? ''
                , Verification::FIELD_NAME_USER_HASH => $request->getHeader(Verification::FIELD_NAME_USER_HASH)[0] ?? ''
            ]
        );

        if (null === $token) {
            return new JsonResponse(['session expired'], IResponse::UNAUTHORIZED);
        }

        return $handler->handle($request->withAttribute(IToken::class, $token));
    }

}

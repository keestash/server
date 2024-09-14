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

use doganoo\SimpleRBAC\Service\RBACServiceInterface;
use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider;
use Keestash\Exception\KeestashException;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class PermissionMiddleware implements MiddlewareInterface {

    public function __construct(
        private IRouterService         $routeService
        , private Config               $config
        , private RBACServiceInterface $rbacService
        , private LoggerInterface      $logger
    ) {
    }

    #[\Override]
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        if (true === $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC)) {
            return $handler->handle($request);
        }

        $permissionMap  = $this->config
            ->get(ConfigProvider::PERMISSIONS)
            ->get(ConfigProvider::PERMISSION_MAPPING)
            ->toArray();
        $permissionFree = $this->config
            ->get(ConfigProvider::PERMISSIONS)
            ->get(ConfigProvider::PERMISSION_FREE)
            ->toArray();
        $path           = $this->routeService->getMatchedPath($request);

        if (in_array($path, $permissionFree, true)) {
            return $handler->handle($request);
        }

        $permissionIds = $permissionMap[$path] ?? null;

        if (null === $permissionIds) {
            $this->logger->error(
                'did not find permissions for path',
                [
                    'path'        => $request->getUri(),
                    'matchedPath' => $path,
                ]
            );
            throw new KeestashException();
        }

        if (false === is_array($permissionIds)) {
            $permissionIds = [$permissionIds];
        }
        // TODO check whether user's role contains permission
        foreach ($permissionIds as $id) {
            $permission = $this->rbacService->getPermission($id);
            if (true === $this->rbacService->hasPermission($token->getUser(), $permission)) {
                return $handler->handle($request);
            }
        }

        return new JsonResponse(['user is not allowed to perform this operation'], IResponse::FORBIDDEN);
    }

}

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


namespace Keestash\Middleware\Api;


use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\Router\IRouterService;
use KSP\Core\Service\User\IUserService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserActiveMiddleware implements MiddlewareInterface {

    private IUserService $userService;

    public function __construct(IUserService $userService) {
        $this->userService = $userService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $routesToInstanceInstall = $request->getAttribute(IRequest::ATTRIBUTE_NAME_ROUTES_TO_INSTANCE_INSTALL, false);
        $routesToInstall         = $request->getAttribute(IRequest::ATTRIBUTE_NAME_ROUTES_TO_INSTALL, false);
        $isPublicRoute           = $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC, false);

        if (
            true === $routesToInstanceInstall
            || true === $routesToInstall
            || true === $isPublicRoute
        ) {
            // We are actually installing the app
            // or the route is public.
            // Therefore, we can not/do not need to check
            // whether there is an user or not
            return $handler->handle($request);
        }

        /** @var IToken|null $token */
        $token = $request->getAttribute(IToken::class);

        if (null === $token || true === $this->userService->isDisabled($token->getUser())) {
            return new JsonResponse(
                'user not given, error 54678'
                , IResponse::NOT_FOUND
            );
        }

        return $handler->handle($request);

    }

}
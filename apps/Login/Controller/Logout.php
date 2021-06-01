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

namespace KSA\Login\Controller;

use Keestash\Core\Manager\SessionManager\SessionManager;
use KSP\App\ILoader;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Logout implements RequestHandlerInterface {

    private ITokenRepository $tokenRepository;
    private SessionManager   $sessionManager;
    private IRouterService   $routerService;
    private ILoader          $loader;
    private RouterInterface  $router;

    public function __construct(
        ITokenRepository $tokenRepository
        , SessionManager $sessionManager
        , IRouterService $routerService
        , ILoader $loader
        , RouterInterface $router
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->sessionManager  = $sessionManager;
        $this->routerService   = $routerService;
        $this->loader          = $loader;
        $this->router          = $router;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $user = $request->getAttribute(IUser::class);
        $this->tokenRepository->removeForUser($user);
        $this->sessionManager->killAll();

        return new RedirectResponse(
            $this->router->generateUri(
                $this->routerService->getRouteByPath($this->loader->getDefaultApp()->getBaseRoute())['name']
            )
        );

    }

}
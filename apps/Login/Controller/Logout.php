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

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Manager\SessionManager\SessionManager;
use Keestash\Exception\KeestashException;
use KSP\Api\IResponse;
use KSP\App\ILoader;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Service\HTTP\IPersistenceService;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Logout implements RequestHandlerInterface {

    private ITokenRepository    $tokenRepository;
    private IRouterService      $routerService;
    private ILoader             $loader;
    private RouterInterface     $router;
    private IPersistenceService $persistenceService;

    public function __construct(
        ITokenRepository      $tokenRepository
        , IRouterService      $routerService
        , ILoader             $loader
        , RouterInterface     $router
        , IPersistenceService $persistenceService
    ) {
        $this->tokenRepository    = $tokenRepository;
        $this->routerService      = $routerService;
        $this->loader             = $loader;
        $this->router             = $router;
        $this->persistenceService = $persistenceService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IUser|null $user */
        $user       = $request->getAttribute(IUser::class);
        $defaultApp = $this->loader->getDefaultApp();
        $this->persistenceService->killAll();

        if (null === $defaultApp) {
            throw new KeestashException();
        }

        if (null !== $user) {
            $this->tokenRepository->removeForUser($user);
        }

        return new RedirectResponse(
            $this->router->generateUri(
                $this->routerService->getRouteByPath($defaultApp->getBaseRoute())['name']
            )
        );

    }

}
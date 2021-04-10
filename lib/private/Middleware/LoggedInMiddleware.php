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
use Keestash\Core\Service\HTTP\HTTPService;
use Keestash\Core\Service\Instance\InstallerService;
use KSP\Core\DTO\User\IUser;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\HTTP\IPersistenceService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Router\Route;
use Mezzio\Router\RouterInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class LoggedInMiddleware implements MiddlewareInterface {

    private IPersistenceService $persistenceService;
    private InstallerService    $installerService;
    private ILogger             $logger;
    private Config              $config;
    private RouterInterface     $router;
    private HTTPService         $httpService;
    private IUserRepository     $userRepository;
    private IEnvironmentService $environmentService;

    public function __construct(
        IPersistenceService $persistenceService
        , InstallerService $installerService
        , ILogger $logger
        , Config $config
        , RouterInterface $router
        , HTTPService $httpService
        , IUserRepository $userRepository
        , IEnvironmentService $environmentService
    ) {
        $this->persistenceService = $persistenceService;
        $this->installerService   = $installerService;
        $this->logger             = $logger;
        $this->config             = $config;
        $this->router             = $router;
        $this->httpService        = $httpService;
        $this->userRepository     = $userRepository;
        $this->environmentService = $environmentService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        if (false === $this->environmentService->isWeb()) {
            return $handler->handle($request);
        }

        if (false === $this->installerService->hasIdAndHash()) {
            // we can not check for this, the instance is
            // not installed and there is no DB
            return $handler->handle($request);
        }

        $publicRoutes = $publicRoutes = $this->config
            ->get(ConfigProvider::WEB_ROUTER)
            ->get(ConfigProvider::PUBLIC_ROUTES)
            ->toArray();

        $currentPath = $this->getMatchedPath($request);
        $userId      = null;
        $persisted   = false;

        foreach ($publicRoutes as $publicRoute) {
            if ($currentPath === $publicRoute) {
                return $handler->handle($request);
            }
        }

        try {
            $userId    = $this->persistenceService->getValue("user_id");
            $persisted = null !== $userId;
        } catch (Throwable $exception) {
            $this->logger->error('error during persistence request ' . $exception->getMessage() . ': ' . $exception->getTraceAsString());
        }

        $user = $this->userRepository->getUserById((string) $userId);
        
        if (true === $persisted
            && null !== $user
        ) {
            return $handler->handle($request->withAttribute(IUser::class, $user));
        }

        // TODO just to be sure: to avoid a "to many redirects", we can check whether
        //  current path equals to login
        return new RedirectResponse(
            $this->httpService->buildWebRoute(ConfigProvider::INSTALL_LOGIN_ROUTE)
        );

    }


    private function getMatchedPath(ServerRequestInterface $request): string {
        $matchedRoute = $this->router->match($request)->getMatchedRoute();

        if ($matchedRoute instanceof Route) {
            return $this->router->match($request)->getMatchedRoute()->getPath();
        }
        return '';
    }


}
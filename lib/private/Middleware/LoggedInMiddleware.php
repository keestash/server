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
use KSP\Api\IRequest;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Environment\IEnvironmentService;
use KSP\Core\Service\HTTP\IPersistenceService;
use KSP\Core\Service\Router\IRouterService;
use Laminas\Config\Config;
use Laminas\Diactoros\Response\RedirectResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Throwable;

class LoggedInMiddleware implements MiddlewareInterface {

    private IPersistenceService $persistenceService;
    private InstallerService    $installerService;
    private ILogger             $logger;
    private HTTPService         $httpService;
    private IUserRepository     $userRepository;

    public function __construct(
        IPersistenceService   $persistenceService
        , InstallerService    $installerService
        , ILogger             $logger
        , HTTPService         $httpService
        , IUserRepository     $userRepository
    ) {
        $this->persistenceService = $persistenceService;
        $this->installerService   = $installerService;
        $this->logger             = $logger;
        $this->httpService        = $httpService;
        $this->userRepository     = $userRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {

        if (false === $this->installerService->hasIdAndHash()) {
            // we can not check for this, the instance is
            // not installed and there is no DB
            return $handler->handle($request);
        }

        $userId    = null;
        $persisted = false;

        if (true === $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC)) {
            return $handler->handle($request);
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
            return $handler->handle($request);
        }

        // TODO just to be sure: to avoid a "to many redirects", we can check whether
        //  current path equals to login
        return new RedirectResponse(
            $this->httpService->buildWebRoute(ConfigProvider::INSTALL_LOGIN_ROUTE)
        );

    }

}
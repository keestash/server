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


namespace Keestash\Middleware\Web;

use Keestash\Core\Service\HTTP\HTTPService;
use KSP\Api\IRequest;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IPersistenceService;
use Psr\Log\LoggerInterface as ILogger;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoggedInMiddleware implements MiddlewareInterface {

    private ILogger         $logger;
    private HTTPService     $httpService;
    private IUserRepository $userRepository;

    public function __construct(
        ILogger           $logger
        , HTTPService     $httpService
        , IUserRepository $userRepository
    ) {
        $this->logger         = $logger;
        $this->httpService    = $httpService;
        $this->userRepository = $userRepository;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $hasIdAndHash  = $request->getAttribute(IRequest::ATTRIBUTE_NAME_INSTANCE_ID_AND_HASH_GIVEN, false);
        $isPublicRoute = $request->getAttribute(IRequest::ATTRIBUTE_NAME_IS_PUBLIC, false);
        $userId        = null;

        if (false === $hasIdAndHash || true === $isPublicRoute) {
            // we can not check for this, the instance is
            // not installed and there is no DB
            // or the route is publicly reachable
            return $handler->handle($request);
        }

        $user = $this->userRepository->getUserById((string) $userId);
        return $handler->handle($request->withAttribute(IUser::class, $user));
    }

}
<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

namespace KSA\Settings\Api\User;

use doganoo\PHPUtil\Datatype\StringClass;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\UserNotCreatedException;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAdd implements RequestHandlerInterface {

    private UserService            $userService;
    private IUserRepositoryService $userRepositoryService;
    private ILogger                $logger;

    public function __construct(
        UserService              $userService
        , IUserRepositoryService $userRepositoryService
        , ILogger                $logger
    ) {
        $this->userService           = $userService;
        $this->userRepositoryService = $userRepositoryService;
        $this->logger                = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters     = json_decode(
            (string) $request->getBody()
            , true
            , 512
            , JSON_THROW_ON_ERROR
        );
        $passwordRepeat = $parameters["password_repeat"];
        $user           = $this->userService->toUser((array) $parameters);

        if (true === $this->userRepositoryService->userExistsByName($user->getName())) return new JsonResponse([], IResponse::NOT_FOUND);
        if (false === (new StringClass($user->getPassword()))->equals($passwordRepeat)) return new JsonResponse([], IResponse::BAD_REQUEST);
        if (false === $this->userService->passwordHasMinimumRequirements($user->getPassword())) return new JsonResponse([], IResponse::BAD_REQUEST);
        if (false === $this->userService->validEmail($user->getEmail())) return new JsonResponse([], IResponse::BAD_REQUEST);
        if (false === $this->userService->validWebsite($user->getWebsite())) return new JsonResponse([], IResponse::BAD_REQUEST);

        $hash = $this->userService->hashPassword($user->getPassword());
        $user->setPassword($hash);

        try {
            $this->userRepositoryService->createUser($user);
            return new JsonResponse(
                []
                , IResponse::OK
            );
        } catch (UserNotCreatedException $exception) {
            $this->logger->error('failed to create user', ['exception' => $exception]);
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

    }

}

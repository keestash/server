<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\Register\Api\User;

use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class Exists implements RequestHandlerInterface {

    private IUserRepository $userRepository;
    private LoggerInterface $logger;

    public function __construct(
        IUserRepository   $userRepository
        , LoggerInterface $logger
    ) {
        $this->userRepository = $userRepository;
        $this->logger         = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $userName  = $request->getAttribute("userName");
        $userFound = false;

        try {
            $this->userRepository->getUser((string) $userName);
            $userFound = true;
        } catch (UserNotFoundException $exception) {
            $this->logger->warning('no user found', ['exception' => $exception]);
        }

        return new JsonResponse(
            [
                "user_exists" => $userFound
            ]
            , IResponse::OK
        );
    }

}

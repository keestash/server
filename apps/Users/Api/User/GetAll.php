<?php
declare(strict_types=1);
/**
 * server
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

namespace KSA\Users\Api\User;

use Keestash\Api\Response\LegacyResponse;
use KSP\Api\IResponse;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class GetAll implements RequestHandlerInterface {

    private ILogger         $logger;
    private IUserRepository $userRepository;

    public function __construct(
        ILogger $logger
        , IUserRepository $userRepository
    ) {
        $this->logger         = $logger;
        $this->userRepository = $userRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "users" => $this->userRepository->getAll()
            ]
        );
    }

}

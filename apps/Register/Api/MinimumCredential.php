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

namespace KSA\Register\Api;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\Service\User\IUserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class MinimumCredential implements RequestHandlerInterface {

    private IUserService $userService;

    public function __construct(IUserService $userService) {
        $this->userService = $userService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $password = $request->getQueryParams()["password"] ?? null;

        if (null === $password) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $hasRequirements = $this->userService->passwordHasMinimumRequirements((string) $password);
        return new JsonResponse(
            [
                'valid' => $hasRequirements
            ]
            , IResponse::OK
        );
    }

}

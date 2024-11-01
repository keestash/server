<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Settings\Api\User\Edit\Password;

use Keestash\Api\Response\JsonResponse;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final readonly class UpdatePassword implements RequestHandlerInterface {

    public function __construct(
        private IUserService             $userService
        , private IUserRepositoryService $userRepositoryService
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {
        /** @var IToken $token */
        $token       = $request->getAttribute(IToken::class);
        $user        = $token->getUser();
        $parameters  = (array) $request->getParsedBody();
        $userId      = (int) ($parameters['userId'] ?? 0);
        $oldPassword = $parameters['oldPassword'] ?? '';
        $password    = $parameters['password'] ?? '';
        $newKey      = $parameters['key'] ?? '';

        // TODO check whether key exists
        if ($user->getId() !== $userId) {
            // TODO change with permission system
            return new JsonResponse([], IResponse::NOT_ALLOWED);
        }

        $newUser = clone $user;

        $requirementsMet = $this->userService->passwordHasMinimumRequirements($password);

        if (false === $requirementsMet) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        if (false === $this->userService->verifyPassword($oldPassword, $user->getPassword())) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $newUser->setPassword(
            $this->userService->hashPassword($password)
        );

        $this->userRepositoryService->updateUser(
            $newUser
            , $user
            , base64_decode($newKey)
        );

        return new JsonResponse([], IResponse::OK);
    }


}

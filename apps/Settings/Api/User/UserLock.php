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

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\Service\User\Event\UserStateLockEvent;
use Keestash\Exception\UserNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserLock implements RequestHandlerInterface {

    private IUserRepository      $userRepository;
    private IUserStateRepository $userStateRepository;
    private IEventManager        $eventManager;

    public function __construct(
        IUserRepository        $userRepository
        , IUserStateRepository $userStateRepository
        , IEventManager        $eventManager
    ) {
        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
        $this->eventManager        = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $userId     = (int) ($parameters['user_id'] ?? -1);
        $user       = $request->getAttribute(IToken::class)->getUser();

        if ($userId < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        if ($user->getId() !== $userId) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        try {
            $user = $this->userRepository->getUserById((string) $userId);
        } catch (UserNotFoundException $exception) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        $locked = $this->userStateRepository->lock($user);

        $this->eventManager
            ->execute(
                new UserStateLockEvent(
                    IUserState::USER_STATE_LOCK
                    , $user
                    , $locked
                )
            );

        if (false === $locked) {
            return new JsonResponse([], IResponse::INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse([], IResponse::OK);
    }

}

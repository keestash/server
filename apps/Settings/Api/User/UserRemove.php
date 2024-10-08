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
use Keestash\Core\DTO\User\UserStateName;
use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserStateService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

final readonly class UserRemove implements RequestHandlerInterface {

    public function __construct(
        private IUserRepository     $userRepository
        , private IUserStateService $userStateService
        , private IEventService     $eventManager
        , private LoggerInterface   $logger
    ) {
    }

    #[\Override]
    public function handle(ServerRequestInterface $request): ResponseInterface {

        $parameters = (array) $request->getParsedBody();
        $userId     = (int) ($parameters['user_id'] ?? -1);
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $user  = $token->getUser();
        if ($userId < 1) {
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        if ($user->getId() !== $userId) {
            return new JsonResponse(
                [
                    "message" => "no parameters given"
                ]
                , IResponse::FORBIDDEN
            );
        }

        try {
            $user = $this->userRepository->getUserById((string) $userId);
        } catch (UserNotFoundException $exception) {
            $this->logger->info(
                'no user found'
                , [
                    'userId'      => $userId
                    , 'exception' => $exception
                ]
            );
            return new JsonResponse(
                [
                    "message" => "no user found"
                ]
                , IResponse::NOT_FOUND
            );
        }

        try {
            $this->userStateService->forceDelete($user);
            return new JsonResponse(
                [
                    "message" => "user remove"
                ]
                , IResponse::OK
            );
        } catch (UserStateException $exception) {
            $this->logger->error('error deleting user', ['exception' => $exception]);
            $this->eventManager
                ->execute(
                    new UserStateDeleteEvent(
                        UserStateName::DELETE
                        , $user
                    )
                );
            return new JsonResponse(
                [
                    "message" => "could not delete user"
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

    }

}

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
use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class UserRemove implements RequestHandlerInterface {

    private IUserRepository      $userRepository;
    private IUserStateRepository $userStateRepository;
    private IL10N                $translator;
    private IEventService        $eventManager;
    private LoggerInterface              $logger;

    public function __construct(
        IL10N                  $l10n
        , IUserRepository      $userRepository
        , IUserStateRepository $userStateRepository
        , IEventService        $eventManager
        , LoggerInterface              $logger
    ) {
        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
        $this->translator          = $l10n;
        $this->eventManager        = $eventManager;
        $this->logger              = $logger;
    }

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
                    "message" => $this->translator->translate("no parameters given")
                ]
                , IResponse::FORBIDDEN
            );
        }

        try {
            $user = $this->userRepository->getUserById((string) $userId);
        } catch (UserNotFoundException $exception) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no user found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        try {
            $this->userStateRepository->delete($user);
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("user remove")
                ]
                , IResponse::OK
            );
        } catch (UserStateException $exception) {
            $this->logger->error('error deleting user', ['exception' => $exception]);
            $this->eventManager
                ->execute(
                    new UserStateDeleteEvent(
                        IUserState::USER_STATE_DELETE
                        , $user
                    )
                );
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("could not delete user")
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

    }

}

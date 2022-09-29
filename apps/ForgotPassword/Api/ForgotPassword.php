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

namespace KSA\ForgotPassword\Api;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\UserNotFoundException;
use KSA\ForgotPassword\Event\ForgotPasswordEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\User\IUserService;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ForgotPassword implements RequestHandlerInterface {

    private IUserService         $userService;
    private IUserStateRepository $userStateRepository;
    private IL10N                $translator;
    private IUserRepository      $userRepository;
    private ILogger              $logger;
    private IEventManager        $eventManager;

    public function __construct(
        IUserService           $userService
        , IUserStateRepository $userStateRepository
        , IL10N                $translator
        , IUserRepository      $userRepository
        , ILogger              $logger
        , IEventManager        $eventManager
    ) {
        $this->userService         = $userService;
        $this->userStateRepository = $userStateRepository;
        $this->translator          = $translator;
        $this->userRepository      = $userRepository;
        $this->logger              = $logger;
        $this->eventManager        = $eventManager;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters     = (array) $request->getParsedBody();
        $input          = $parameters["input"] ?? null;
        $responseHeader = $this->translator->translate("Password reset");

        if (null === $input || "" === $input) {
            return new JsonResponse(
                ['no input given']
                , IResponse::BAD_REQUEST
            );
        }

        $userByMail = null;
        $userByName = null;
        try {
            $userByMail = $this->userRepository->getUserByEmail($input);
        } catch (UserNotFoundException $exception) {
            $this->logger->error('no users found', ['exception' => $exception]);
        }
        try {
            $userByName = $this->userRepository->getUser($input);
        } catch (UserNotFoundException $exception) {
            $this->logger->error('no users found', ['exception' => $exception]);
        }

        $user = null === $userByMail
            ? $userByName
            : $userByMail;

        if (null === $user) {
            return new JsonResponse(
                ["No user found"]
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                ["Can not reset the user. Please contact your admin"]
                , IResponse::FORBIDDEN
            );
        }

        $userStates       = $this->userStateRepository->getUsersWithPasswordResetRequest();
        $alreadyRequested = false;

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);
            if ($user->getId() === $userState->getUser()->getId()) {
                $difference       = $userState->getCreateTs()->diff(new DateTimeImmutable());
                $alreadyRequested = $difference->i < 2; // not requested within the last 2 minutes
            }
        }

        if (true === $alreadyRequested) {

            return new JsonResponse(
                [
                    "header"    => $responseHeader
                    , "message" => $this->translator->translate("You have already requested an password reset. Please check your mails or try later again")
                ]
                , IResponse::NOT_ACCEPTABLE
            );

        }

        $this->eventManager->execute(
            new ForgotPasswordEvent($user)
        );

        return new JsonResponse(
            [
                "header" => $responseHeader
            ]
            , IResponse::OK
        );
    }

}

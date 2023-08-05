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

namespace KSA\Register\Api\User;

use DateTimeImmutable;
use Keestash\Api\Response\JsonResponse;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Register\Entity\IResponseCodes;
use KSA\Register\Event\ResetPasswordEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\IUserService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ResetPassword implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserService           $userService
        , private readonly IUserStateRepository $userStateRepository
        , private readonly IUserRepository      $userRepository
        , private readonly LoggerInterface      $logger
        , private readonly IEventService        $eventManager
        , private readonly IResponseService     $responseService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $input      = $parameters["input"] ?? null;

        if (null === $input || "" === $input) {
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_INVALID_INPUT)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $userByMail = null;
        $userByName = null;
        try {
            $userByMail = $this->userRepository->getUserByEmail($input);
        } catch (UserNotFoundException $exception) {
            $this->logger->warning('no users found', ['exception' => $exception]);
        }
        try {
            $userByName = $this->userRepository->getUser($input);
        } catch (UserNotFoundException $exception) {
            $this->logger->warning('no users found', ['exception' => $exception]);
        }

        $user = null === $userByMail
            ? $userByName
            : $userByMail;

        if (null === $user) {
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_USER_NOT_FOUND)
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_USER_DISABLED)
                ]
                , IResponse::FORBIDDEN
            );
        }

        $userStates       = $this->userStateRepository->getUsersWithPasswordResetRequest();
        $alreadyRequested = false;

        /** @var IUserState $userState */
        foreach ($userStates->toArray() as $userState) {
            if ($user->getId() === $userState->getUser()->getId()) {
                $difference       = $userState->getCreateTs()->diff(new DateTimeImmutable());
                $alreadyRequested = $difference->i < 2; // not requested within the last 2 minutes
            }
        }

        if (true === $alreadyRequested) {

            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_ALREADY_SENT)
                ]
                , IResponse::NOT_ACCEPTABLE
            );

        }

        $this->eventManager->execute(
            new ResetPasswordEvent($user)
        );

        return new JsonResponse(
            [
                "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_RESET_MAIL_SENT)
            ]
            , IResponse::OK
        );
    }

}

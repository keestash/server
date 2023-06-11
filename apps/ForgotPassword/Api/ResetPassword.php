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

use DateTime;
use KSA\ForgotPassword\Event\ResetPasswordEvent;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

class ResetPassword implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserStateRepository     $userStateRepository
        , private readonly IUserService           $userService
        , private readonly IUserRepositoryService $userRepositoryService
        , private readonly IEventService          $eventManager
        , private readonly LoggerInterface        $logger
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $this->logger->debug(
            'reset password flow',
            [
                'stage' => 'start'
            ]
        );
        $parameters  = (array) $request->getParsedBody();
        $hash        = $parameters["hash"] ?? '';
        $newPassword = $parameters["input"] ?? '';

        if ("" === $hash || "" === $newPassword) {
            return new JsonResponse([], IResponse::NOT_ACCEPTABLE);
        }

        $debug = $request->getAttribute(IRequest::ATTRIBUTE_NAME_DEBUG, false);

        $userState = $this->findCandidate($hash, $debug);

        if (null === $userState) {
            return new JsonResponse(
                [
                    "responseCode" => 133909
                ]
                , IResponse::NOT_FOUND
            );
        }
        $validPassword = $this->userService->passwordHasMinimumRequirements($newPassword);
        if (false === $validPassword) {
            return new JsonResponse(
                [
                    "header"    => "User not updated"
                    , "message" => "Password minimum requirements not met"
                ]
                , IResponse::NOT_ACCEPTABLE
            );
        }

        $newUser = clone $userState->getUser();

        $newUser->setPassword(
            $this->userService->hashPassword($newPassword)
        );

        $this->logger->debug(
            'reset password flow',
            [
                'stage'   => 'new user password set',
                'oldUser' => [
                    'id'       => $userState->getUser()->getId(),
                    'password' => $userState->getUser()->getPassword()
                ],
                'newUser' => [
                    'id'       => $newUser->getId(),
                    'password' => $newUser->getPassword()
                ]
            ]
        );
        $this->userRepositoryService->updateUser($newUser, $userState->getUser());
        $this->userStateRepository->revertPasswordChangeRequest($userState->getUser());

        $this->eventManager->execute(new ResetPasswordEvent());

        $this->logger->debug(
            'reset password flow',
            [
                'stage' => 'end'
            ]
        );
        return new JsonResponse(
            [
                "header"    => "User updated"
                , "message" => "We sent an email to reset your password"
            ]
            , IResponse::OK
        );

    }

    private function findCandidate(string $hash, bool $debug = false): ?IUserState {
        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);

            if (
                true === $debug
                || ($userState->getStateHash() === $hash
                    && $userState->getCreateTs()->diff(new DateTime())->i < 2)
            ) {
                return $userState;
            }

        }

        return null;
    }

}

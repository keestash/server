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

use DateTime;
use Keestash\Core\DTO\User\NullUserState;
use KSA\Register\Entity\IResponseCodes;
use KSA\Register\Event\ResetPasswordConfirmEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\IUserStateService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;

readonly final class ResetPasswordConfirm implements RequestHandlerInterface {

    public function __construct(
        private IUserStateRepository     $userStateRepository
        , private IUserService           $userService
        , private IUserRepositoryService $userRepositoryService
        , private IEventService          $eventManager
        , private LoggerInterface        $logger
        , private IResponseService       $responseService
        , private IUserStateService      $userStateService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $this->logger->debug(
            'reset password flow',
            [
                'stage' => 'start'
            ]
        );
        $parameters = (array) $request->getParsedBody();
        $hash       = $parameters["hash"] ?? '';
        $password   = $parameters["password"] ?? '';

        $userState = $this->findCandidate($hash);

        if ($userState instanceof NullUserState) {
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_CONFIRM_USER_BY_HASH_NOT_FOUND)
                ]
                , IResponse::NOT_FOUND
            );
        }

        $validPassword = $this->userService->passwordHasMinimumRequirements($password);
        if (false === $validPassword) {
            return new JsonResponse(
                [
                    "responseCode" => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_RESET_PASSWORD_CONFIRM_INVALID_PASSWORD)
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $user       = $userState->getUser();
        $updateUser = clone $user;

        $updateUser->setPassword(
            $this->userService->hashPassword($password)
        );

        $this->userRepositoryService->updateUser($updateUser, $user);
        $this->userStateService->clearCarefully($user, IUserState::USER_STATE_REQUEST_PW_CHANGE);

        $this->eventManager->execute(new ResetPasswordConfirmEvent(2));

        $this->logger->debug(
            'reset password flow',
            [
                'stage' => 'end'
            ]
        );
        return new JsonResponse(
            []
            , IResponse::OK
        );

    }

    private function findCandidate(string $hash): IUserState {
        $userState = $this->userStateRepository->getByHash($hash);
        if ($userState->getCreateTs()->diff(new DateTime())->i < 2) {
            return $userState;
        }
        return new NullUserState();
    }

}

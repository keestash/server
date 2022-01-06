<?php
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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
use Keestash\Api\Response\JsonResponse;
use KSP\Api\IRequest;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AccountDetails implements RequestHandlerInterface {

    private IUserStateRepository $userStateRepository;

    public function __construct(IUserStateRepository $userStateRepository) {
        $this->userStateRepository = $userStateRepository;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $token = $request->getAttribute('resetPasswordToken');
        $user  = null;
        $debug = $request->getAttribute(IRequest::ATTRIBUTE_NAME_DEBUG, false);

        if (null === $token) {
            return new JsonResponse(
                []
                , IResponse::FORBIDDEN
            );
        }

        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $usersState */
            $usersState = $userStates->get($userStateId);
            if ($token === $usersState->getStateHash()) {
                $user = $usersState->getUser();
                break;
            }
        }

        if (null === $user) {
            return new JsonResponse(
                []
                , IResponse::NOT_FOUND
            );
        }

        return new JsonResponse(
            [
                "token"     => $token
                , "hasHash" => $debug || $this->hasHash($token)

            ]
            , IResponse::OK
        );
    }

    private function hasHash(?string $hash): bool {
        if (null === $hash) return false;
        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);

            if (
                $userState->getStateHash() === $hash
                && $userState->getCreateTs()->diff(new DateTime())->i < 2
            ) {
                return true;
            }

        }

        return false;
    }

}
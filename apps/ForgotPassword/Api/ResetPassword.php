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
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class ResetPassword implements RequestHandlerInterface {

    private IUserStateRepository $userStateRepository;
    private UserService          $userService;
    private IL10N                $translator;

    public function __construct(
        IL10N $l10n
        , IUserStateRepository $userStateRepository
        , UserService $userService
    ) {
        $this->userStateRepository = $userStateRepository;
        $this->userService         = $userService;
        $this->translator          = $l10n;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters  = json_decode($request->getBody()->getContents(), true);
        $hash        = $parameters["hash"] ?? null;
        $newPassword = $parameters["input"] ?? null;

        $userState     = $this->findCandidate($hash);
        $validPassword = $this->userService->passwordHasMinimumRequirements($newPassword);

        if (null === $userState) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $this->translator->translate("User not updated")
                    , "message" => $this->translator->translate("No user found or session is expired. Please request a new link")
                ]
            );

        }

        if (false === $validPassword) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "header"    => $this->translator->translate("User not updated")
                    , "message" => $this->translator->translate("Password minimum requirements not met")
                ]
            );

        }

        $newUser = $userState->getUser();
        $oldUser = clone $newUser;

        $newUser->setPassword(
            $this->userService->hashPassword($newPassword)
        );

        $updated = $this->userService->updateUser($newUser, $oldUser);

        if (true === $updated) {

            $this->userStateRepository->revertPasswordChangeRequest($oldUser);

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_OK
                , [
                    "header"    => $this->translator->translate("User updated")
                    , "message" => $this->translator->translate("We sent an email to reset your password")
                ]
            );

        }

        return new JsonResponse(
            [],
            500
        );
    }

    private function findCandidate(string $hash): ?IUserState {
        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);

            if (
                $userState->getStateHash() === $hash
                && $userState->getCreateTs()->diff(new DateTime())->i < 2
            ) {
                return $userState;
            }

        }

        return null;
    }

    public function afterCreate(): void {

    }

}

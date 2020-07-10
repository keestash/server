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

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\DefaultResponse;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class ResetPassword extends AbstractApi {

    /** @var IUserStateRepository */
    private $userStateRepository;
    /** @var UserService */
    private $userService;

    public function __construct(
        IL10N $l10n
        , IUserStateRepository $userStateRepository
        , UserService $userService
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userStateRepository = $userStateRepository;
        $this->userService         = $userService;
    }

    public function onCreate(array $parameters): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $hash        = $this->getParameter("hash", null);
        $newPassword = $this->getParameter("input", null);


        // TODO input validation here
        // 1. hash exists
        // 2. hash expired
        // 3. password minimum requirements

        $userState = $this->findCandidate($hash);

        $newUser = $userState->getUser();
        $oldUser = clone $newUser;

        $newUser->setPassword(
            $this->userService->hashPassword($newPassword)
        );

        $updated = $this->userService->updateUser($newUser, $oldUser);

        if (true === $updated) {

            $this->userStateRepository->revertPasswordChangeRequest($oldUser);

            $response = new DefaultResponse();
            $response->addMessage(
                IResponse::RESPONSE_CODE_OK
                , [
                    "header"    => $this->getL10N()->translate("User updated")
                    , "message" => $this->getL10N()->translate("We sent an email to reset your password")
                ]
            );
            $this->setResponse($response);
            return;

        }

    }

    private function findCandidate(string $hash): ?IUserState {
        $userStates = $this->userStateRepository->getUsersWithPasswordResetRequest();

        foreach ($userStates->keySet() as $userStateId) {
            /** @var IUserState $userState */
            $userState = $userStates->get($userStateId);
            if ($userState->getStateHash() === $hash) {
                return $userState;
            }
        }

        return null;
    }

    public function afterCreate(): void {

    }

}
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

namespace KSA\Users\Api\User;

use Keestash;
use Keestash\Api\AbstractApi;

use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class UserRemove extends AbstractApi {

    /** @var IUserRepository $userRepository */
    private $userRepository;

    /** @var IUserStateRepository $userStateRepository */
    private $userStateRepository;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , IUserStateRepository $userStateRepository
        , ?IToken $token = null
    ) {
        parent::__construct($l10n, $token);

        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $userId = $this->getParameter('user_id', '');

        if ("" === $userId) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no parameters given")
                ]
            );

            return;
        }

        $user = $this->userRepository->getUserById((string) $userId);

        if (null === $user) {

            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("no user found")
                ]
            );

            return;
        }

        $deleted = $this->userStateRepository->delete($user);

        Keestash::getServer()
            ->getEventManager()
            ->execute(
                new UserStateDeleteEvent(
                    IUserState::USER_STATE_DELETE
                    , $user
                    , $deleted
                )
            );

        if (false === $deleted) {
            $this->createAndSetResponse(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->getL10N()->translate("could not delete user")
                ]
            );

            return;
        }

        $this->createAndSetResponse(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->getL10N()->translate("user remove")
            ]
        );

        return;
    }

    public function afterCreate(): void {

    }

}

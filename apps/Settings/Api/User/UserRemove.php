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

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\User\Event\UserStateDeleteEvent;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\Manager\EventManager\IEventManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserRemove implements RequestHandlerInterface {

    private IUserRepository      $userRepository;
    private IUserStateRepository $userStateRepository;
    private IL10N                $translator;
    private IEventManager        $eventManager;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , IUserStateRepository $userStateRepository
        , IEventManager $eventManager
    ) {
        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
        $this->translator          = $l10n;
        $this->eventManager        = $eventManager;
    }


    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string)$request->getBody(), true);
        $userId     = $parameters['user_id'] ?? '';

        if ("" === $userId) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no parameters given")
                ]
            );
        }

        $user = $this->userRepository->getUserById((string) $userId);

        if (null === $user) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no user found")
                ]
            );
        }

        $deleted = $this->userStateRepository->delete($user);

        $this->eventManager
            ->execute(
                new UserStateDeleteEvent(
                    IUserState::USER_STATE_DELETE
                    , $user
                    , $deleted
                )
            );

        if (false === $deleted) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("could not delete user")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->translator->translate("user remove")
            ]
        );
    }

}

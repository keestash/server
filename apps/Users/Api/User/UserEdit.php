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

use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\Service\User\UserService;
use KSA\Users\Exception\UsersException;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserEdit implements RequestHandlerInterface {

    const FIRSTNAME = "firstname";
    const LASTNAME  = "lastname";
    const PASSWORD  = "password";
    const EMAIL     = "email";
    const PHONE     = "phone";
    const WEBSITE   = "website";

    private IUserRepository $userRepository;
    private UserService     $userService;
    private IL10N           $translator;

    public function __construct(
        IL10N $l10n
        , IUserRepository $userRepository
        , UserService $userService
    ) {
        $this->userRepository = $userRepository;
        $this->userService    = $userService;
        $this->translator     = $l10n;
    }

    private function hasDifferences(IUser $repoUser, IUser $newUser): bool {
        if ($repoUser->getName() !== $newUser->getName()) return true;
        if ($repoUser->getFirstName() !== $newUser->getFirstName()) return true;
        if ($repoUser->getLastName() !== $newUser->getLastName()) return true;
        if ($repoUser->getEmail() !== $newUser->getEmail()) return true;
        if ($repoUser->getPhone() !== $newUser->getPhone()) return true;
        if ($repoUser->isLocked() !== $newUser->isLocked()) return true;
        if ($repoUser->isDeleted() !== $newUser->isDeleted()) return true;
        return false;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = json_decode((string)$request->getBody(), true);
        $user       = $this->userService->toUser($parameters['user']);
        $repoUser   = $this->userRepository->getUserById((string) $user->getId());

        if (null === $repoUser) {
            throw new UsersException();
        }

        $oldUser = clone $repoUser;

        if (false === $this->hasDifferences($repoUser, $user)) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_OK
                , [
                    'message' => $this->translator->translate("no differences detected")
                ]
            );
        }

        if (true === $this->userService->isDisabled($repoUser)) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("no user found")
                ]
            );
        }

        $repoUser->setName($user->getName());
        $repoUser->setFirstName($user->getFirstName());
        $repoUser->setLastName($user->getLastName());
        $repoUser->setEmail($user->getEmail());
        $repoUser->setPhone($user->getPhone());
        $repoUser->setLocked($user->isLocked());
        $repoUser->setDeleted($user->isDeleted());

        $updated = $this->userService->updateUser($repoUser, $oldUser);

        if (false === $updated) {
            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_NOT_OK
                , [
                    "message" => $this->translator->translate("user could not be found")
                ]
            );
        }

        return LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_OK
            , [
                "message" => $this->translator->translate("user updated"),
                "user_id" => $repoUser->getId()
            ]
        );
    }

}

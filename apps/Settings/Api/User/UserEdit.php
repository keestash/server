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

use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\User\UserService;
use KSA\Settings\Exception\SettingsException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use KSP\L10N\IL10N;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserEdit implements RequestHandlerInterface {

    private IUserRepository        $userRepository;
    private UserService            $userService;
    private IL10N                  $translator;
    private IUserRepositoryService $userRepositoryService;
    private IJWTService            $jwtService;

    public function __construct(
        IL10N                    $l10n
        , IUserRepository        $userRepository
        , UserService            $userService
        , IUserRepositoryService $userRepositoryService
        , IJWTService            $jwtService
    ) {
        $this->userRepository        = $userRepository;
        $this->userService           = $userService;
        $this->translator            = $l10n;
        $this->userRepositoryService = $userRepositoryService;
        $this->jwtService            = $jwtService;
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
        $parameters = json_decode((string) $request->getBody(), true);
        $user       = $this->userService->toUser($parameters['user']);
        $repoUser   = $this->userRepository->getUserById((string) $user->getId());

        if (null === $repoUser) {
            throw new SettingsException();
        }

        $oldUser = clone $repoUser;

        if (false === $this->hasDifferences($repoUser, $user)) {
            return new JsonResponse(
                [
                    'message' => $this->translator->translate("no differences detected")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        if (true === $this->userService->isDisabled($repoUser)) {

            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no user found")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        $repoUser->setName($user->getName());
        $repoUser->setFirstName($user->getFirstName());
        $repoUser->setLastName($user->getLastName());
        $repoUser->setEmail($user->getEmail());
        $repoUser->setPhone($user->getPhone());
        $repoUser->setLocked($user->isLocked());
        $repoUser->setDeleted($user->isDeleted());
        $repoUser->setJWT(
            $this->jwtService->getJWT(
                new Audience(
                    IAudience::TYPE_USER
                    , (string) $repoUser->getId()
                )
            )
        );


        $updated = $this->userRepositoryService->updateUser($repoUser, $oldUser);

        if (false === $updated) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("user could not be found")
                ]
                , IResponse::INTERNAL_SERVER_ERROR
            );
        }

        return new JsonResponse(
            [
                "user" => $repoUser
            ]
            , IResponse::OK
        );
    }

}

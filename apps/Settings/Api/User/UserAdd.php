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

use DateTime;
use doganoo\PHPUtil\Datatype\StringClass;
use Keestash\Api\Response\LegacyResponse;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Service\User\UserService;
use KSP\Api\IResponse;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class UserAdd implements RequestHandlerInterface {

    private UserService            $userService;
    private IUserRepository        $userRepository;
    private IUserRepositoryService $userRepositoryService;

    public function __construct(
        UserService $userService
        , IUserRepository $userManager
        , IUserRepositoryService $userRepositoryService
    ) {
        $this->userService           = $userService;
        $this->userRepository        = $userManager;
        $this->userRepositoryService = $userRepositoryService;
    }

    private function toUser(array $params): IUser {
        $userName  = $params[0];
        $firstName = $params[1];
        $lastName  = $params[2];
        $email     = $params[3];
        $phone     = $params[4];
        $password  = $params[5];
        $website   = $params[7];

        $user = new User();
        $user->setName($userName);
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setPassword($password);
        $user->setCreateTs(new DateTime());
        $user->setWebsite($website);
        $user->setHash($this->userService->getRandomHash());

        return $user;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {

        $parameters     = json_decode((string) $request->getBody(), true);
        $passwordRepeat = $parameters["password_repeat"];
        $user           = $this->toUser($parameters);

        $response = LegacyResponse::fromData(
            IResponse::RESPONSE_CODE_NOT_OK
            , []
        );
        if (true === $this->userRepositoryService->userExistsByName($user->getName())) return $response;
        if (false === (new StringClass($user->getPassword()))->equals($passwordRepeat)) return $response;
        if (false === $this->userService->passwordHasMinimumRequirements($user->getPassword())) return $response;
        if (false === $this->userService->validEmail($user->getEmail())) return $response;
        if (false === $this->userService->validWebsite($user->getWebsite())) return $response;

        $hash = $this->userService->hashPassword($user->getPassword());
        $user->setPassword($hash);

        $userId = $this->userRepository->insert($user);

        if (null !== $userId) {

            return LegacyResponse::fromData(
                IResponse::RESPONSE_CODE_OK
                , [
                    "User Created"
                ]
            );
        }

        return $response;
    }

}

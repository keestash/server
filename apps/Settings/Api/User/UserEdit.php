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

use doganoo\SimpleRBAC\Service\RBACServiceInterface;
use Keestash\Api\Response\JsonResponse;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Settings\Entity\IResponseCodes;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\RBAC\IPermission;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use TypeError;

class UserEdit implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserRepository          $userRepository
        , private readonly UserService            $userService
        , private readonly IUserRepositoryService $userRepositoryService
        , private readonly IJWTService            $jwtService
        , private readonly LoggerInterface        $logger
        , private readonly RBACServiceInterface   $rbacService
        , private readonly IResponseService       $responseService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $userArray    = $parameters['user'] ?? [];
        $userIdToEdit = (int) ($userArray['id'] ?? -1);
        /** @var IToken $token */
        $token = $request->getAttribute(IToken::class);
        $user  = $token->getUser();

        try {
            $repoUser = $this->userRepository->getUserById((string) $userIdToEdit);
        } catch (UserNotFoundException $exception) {
            $this->logger->warning(
                'no user found'
                , [
                    'exception'    => $exception
                    , 'userToEdit' => $userIdToEdit
                ]
            );
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (false === $this->hasPermissionToEditOtherUsers($user, $repoUser)) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }


        if (true === $this->userService->isDisabled($repoUser)) {

            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_USER_DISABLED)
                ], IResponse::BAD_REQUEST
            );
        }
        $oldUser = clone $repoUser;

        try {
            $param         = $userArray['locale'];
            $splittedParam = explode('_', $param);
            $language      = strtolower($splittedParam[0]);
            $locale        = strtolower($splittedParam[1]);

            $languageUpdated =
                $language !== strtolower($user->getLanguage())
                && $locale !== strtolower($user->getLocale());
            $repoUser->setName($userArray['name']);
            $repoUser->setFirstName($userArray['first_name']);
            $repoUser->setLastName($userArray['last_name']);
            $repoUser->setEmail($userArray['email']);
            $repoUser->setPhone($userArray['phone']);
            $repoUser->setLocked($userArray['locked']);
            $repoUser->setDeleted($userArray['deleted']);
            $repoUser->setLanguage($language);
            $repoUser->setLocale($locale);
            $repoUser->setJWT(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_USER
                        , (string) $repoUser->getId()
                    )
                )
            );
        } catch (TypeError $error) {
            $this->logger->error(
                'error creating user',
                [
                    'error'      => [
                        'message' => $error->getMessage(),
                        'trace'   => $error->getTraceAsString()
                    ],
                    'parameters' => $userArray
                ]
            );
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $repoUser = $this->userRepositoryService->updateUser($repoUser, $oldUser);
        return new JsonResponse(
            [
                "user"              => $repoUser
                , 'languageUpdated' => $languageUpdated
            ]
            , IResponse::OK
        );
    }

    private function hasPermissionToEditOtherUsers(IUser $me, IUser $other): bool {
        if ($me->getId() === $other->getId()) {
            return true;
        }
        return $this->rbacService->hasPermission($me, $this->rbacService->getPermission(IPermission::PERMISSION_USERS_EDIT_OTHER_USERS));
    }

}

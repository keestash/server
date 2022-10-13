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
use Keestash\Exception\User\UserNotFoundException;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\L10N\IL10N;
use Psr\Log\LoggerInterface as ILogger;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TypeError;

class UserEdit implements RequestHandlerInterface {

    private IUserRepository        $userRepository;
    private UserService            $userService;
    private IL10N                  $translator;
    private IUserRepositoryService $userRepositoryService;
    private IJWTService            $jwtService;
    private ILogger                $logger;

    public function __construct(
        IL10N                    $l10n
        , IUserRepository        $userRepository
        , UserService            $userService
        , IUserRepositoryService $userRepositoryService
        , IJWTService            $jwtService
        , ILogger                $logger
    ) {
        $this->userRepository        = $userRepository;
        $this->userService           = $userService;
        $this->translator            = $l10n;
        $this->userRepositoryService = $userRepositoryService;
        $this->jwtService            = $jwtService;
        $this->logger                = $logger;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters   = (array) $request->getParsedBody();
        $userIdToEdit = (int) ($parameters['id'] ?? -1);
        $user         = $request->getAttribute(IToken::class)->getUser();

        try {
            $repoUser = $this->userRepository->getUserById((string) $userIdToEdit);
        } catch (UserNotFoundException $exception) {
            $this->logger->warning('no user found', ['exception' => $exception]);
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        // TODO $user has permission to edit users and/or update organizations
        //  this constraint could be limiting
        if ($user->getId() !== $userIdToEdit) {
            return new JsonResponse([], IResponse::FORBIDDEN);
        }

        $oldUser = clone $repoUser;

        if (true === $this->userService->isDisabled($repoUser)) {

            return new JsonResponse(
                [
                    "message" => $this->translator->translate("no user found")
                ]
                , IResponse::BAD_REQUEST
            );
        }

        try {
            $repoUser->setName($parameters['name']);
            $repoUser->setFirstName($parameters['first_name']);
            $repoUser->setLastName($parameters['last_name']);
            $repoUser->setEmail($parameters['email']);
            $repoUser->setPhone($parameters['phone']);
            $repoUser->setLocked($parameters['locked']);
            $repoUser->setDeleted($parameters['deleted']);
            $repoUser->setLanguage($parameters['language']);
            $repoUser->setLocale($parameters['locale']);
            $repoUser->setJWT(
                $this->jwtService->getJWT(
                    new Audience(
                        IAudience::TYPE_USER
                        , (string) $repoUser->getId()
                    )
                )
            );
        } catch (TypeError $error) {
            $this->logger->error('error creating user', ['error' => $error, 'parameters' => $parameters]);
            return new JsonResponse([], IResponse::BAD_REQUEST);
        }

        $repoUser = $this->userRepositoryService->updateUser($repoUser, $oldUser);
        return new JsonResponse(
            [
                "user" => $repoUser
            ]
            , IResponse::OK
        );
    }

}

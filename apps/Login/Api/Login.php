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

namespace KSA\Login\Api;

use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Router\Verification;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\UserNotFoundException;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\L10N\IL10N;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Login implements RequestHandlerInterface {

    private IUserRepository  $userRepository;
    private IL10N            $translator;
    private UserService      $userService;
    private ITokenRepository $tokenRepository;
    private TokenService     $tokenService;
    private ILocaleService   $localeService;
    private ILanguageService $languageService;
    private InstanceDB       $instanceDB;
    private IJWTService      $jwtService;

    public function __construct(
        IUserRepository    $userRepository
        , IL10N            $translator
        , UserService      $userService
        , ITokenRepository $tokenManager
        , TokenService     $tokenService
        , ILocaleService   $localeService
        , ILanguageService $languageService
        , InstanceDB       $instanceDB
        , IJWTService      $jwtService
    ) {
        $this->userRepository  = $userRepository;
        $this->translator      = $translator;
        $this->userService     = $userService;
        $this->tokenRepository = $tokenManager;
        $this->tokenService    = $tokenService;
        $this->localeService   = $localeService;
        $this->languageService = $languageService;
        $this->instanceDB      = $instanceDB;
        $this->jwtService      = $jwtService;
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $isDemoMode = $this->instanceDB->getOption("demo") === "true";
        $parameters = (array) $request->getParsedBody();
        $userName   = $parameters["user"] ?? "";
        $password   = $parameters["password"] ?? "";

        try {
            $user     = $this->userRepository->getUser($userName);
            $demoUser = $this->userService->getDemoUser();
        } catch (UserNotFoundException $exception) {
            return new JsonResponse(
                'no user found'
                , IResponse::NOT_FOUND
            );
        }
        if (true === $isDemoMode && $user->getId() !== $demoUser->getId()) {
            return new JsonResponse([], IResponse::NOT_FOUND);
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("No User Found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (false === $this->userService->verifyPassword($password, $user->getPassword())) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("Invalid Credentials")
                ]
                , IResponse::UNAUTHORIZED
            );
        }
        $token = $this->tokenService->generate("login", $user);

        $this->tokenRepository->add($token);

        $user->setJWT(
            $this->jwtService->getJWT(
                new Audience(
                    IAudience::TYPE_USER
                    , (string) $user->getId()
                )
            )
        );
        return new JsonResponse(
            [
                "settings" => [
                    "locale"     => $this->localeService->getLocaleForUser($user)
                    , "language" => $this->languageService->getLanguageForUser($user)
                ],
                "user"     => $user
            ],
            IResponse::OK
            , [
                Verification::FIELD_NAME_TOKEN       => $token->getValue()
                , Verification::FIELD_NAME_USER_HASH => $user->getHash()
            ]
        );

    }

}

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

use DateTimeImmutable;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\Token\TokenNotCreatedException;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\L10N\IL10N;
use KSP\Core\Service\LDAP\ILDAPService;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;

class Login implements RequestHandlerInterface {

    public function __construct(
        private readonly IUserRepository         $userRepository
        , private readonly IL10N                 $translator
        , private readonly UserService           $userService
        , private readonly ITokenRepository      $tokenRepository
        , private readonly TokenService          $tokenService
        , private readonly ILocaleService        $localeService
        , private readonly ILanguageService      $languageService
        , private readonly IJWTService           $jwtService
        , private readonly LoggerInterface       $logger
        , private readonly ILDAPService          $ldapService
        , private readonly IConnectionRepository $connectionRepository
        , private readonly IDerivationRepository $derivationRepository
        , private readonly IDerivationService    $derivationService
    ) {
    }

    /**
     * @param ServerRequestInterface $request
     * @return ResponseInterface
     * @throws TokenNotCreatedException
     * TODO add demo mode
     */
    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $userName   = $parameters["user"] ?? "";
        $password   = $parameters["password"] ?? "";
        $isSaas     = $request->getAttribute(ConfigProvider::ENVIRONMENT_SAAS);

        try {
            $user = $this->userRepository->getUser($userName);
        } catch (UserNotFoundException $exception) {
            $this->logger->error(
                'error retrieving user',
                [
                    'exception' => $exception,
                    'userName'  => $userName,
                ]
            );
            return new JsonResponse(
                'no user found'
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("No User Found")
                ]
                , IResponse::NOT_FOUND
            );
        }

        $verified = false;
        if (true === $user->isLdapUser()) {
            $verified = $this->ldapService->verifyUser(
                $user
                , $this->connectionRepository->getConnectionByUser($user)
                , $password
            );
        } else {
            $this->logger->debug('verifying regular user');
            $verified = $this->userService->verifyPassword($password, $user->getPassword());
        }

        if (false === $verified) {
            return new JsonResponse(
                [
                    "message" => $this->translator->translate("Invalid Credentials")
                ]
                , IResponse::UNAUTHORIZED
            );
        }
        $token = $this->tokenService->generate("login", $user);
        $this->tokenRepository->add($token);

        $this->derivationRepository->clear($user);
        $this->derivationRepository->add(
            new Derivation(
                Uuid::uuid4()->toString()
                , $user
                , $this->derivationService->derive($user->getPassword())
                , new DateTimeImmutable()
            )
        );

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
                    , "isSaas"   => true === $isSaas
                ],
                "user"     => $user
            ],
            IResponse::OK
            , [
                VerificationService::FIELD_NAME_TOKEN       => $token->getValue()
                , VerificationService::FIELD_NAME_USER_HASH => $user->getHash()
            ]
        );

    }

}

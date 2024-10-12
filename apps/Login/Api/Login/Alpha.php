<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Login\Api\Login;

use Keestash\Api\Response\JsonResponse;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\Http\JWT\Audience;
use Keestash\Core\DTO\User\NullUser;
use Keestash\Core\Service\Router\VerificationService;
use Keestash\Core\Service\User\UserService;
use KSA\Login\Entity\IResponseCodes;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Http\JWT\IAudience;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Encryption\Key\IKeyService;
use KSP\Core\Service\HTTP\IJWTService;
use KSP\Core\Service\HTTP\IResponseService;
use KSP\Core\Service\LDAP\ILDAPService;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final readonly class Alpha {

    public function __construct(
        private IUserRepository         $userRepository
        , private UserService           $userService
        , private ITokenRepository      $tokenRepository
        , private TokenService          $tokenService
        , private ILocaleService        $localeService
        , private ILanguageService      $languageService
        , private IJWTService           $jwtService
        , private LoggerInterface       $logger
        , private ILDAPService          $ldapService
        , private IConnectionRepository $connectionRepository
        , private IDerivationService    $derivationService
        , private IResponseService      $responseService
        , private IKeyService           $keyService
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface {
        $parameters = (array) $request->getParsedBody();
        $userName   = $parameters["user"] ?? "";
        $password   = $parameters["password"] ?? "";
        $isSaas     = $request->getAttribute(ConfigProvider::ENVIRONMENT_SAAS);

        $user = $this->userRepository->getUser($userName);

        if ($user instanceof NullUser) {
            $this->logger->error(
                'error retrieving user',
                [
                    'userName' => $userName
                ]
            );
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_USER_NOT_FOUND)
                ]
                , IResponse::NOT_FOUND
            );
        }

        if (true === $this->userService->isDisabled($user)) {
            $this->logger->error(
                'tried to log in with an disabled user',
                [
                    'userName' => $userName,
                ]
            );
            return new JsonResponse(
                [
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_USER_DISABLED)
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
                    'responseCode' => $this->responseService->getResponseCode(IResponseCodes::RESPONSE_NAME_INVALID_CREDENTIALS)
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

        $key = $this->keyService->getKey($user);
        return new JsonResponse(
            data: [
                "settings"   => [
                    "locale"   => $this->localeService->getLocaleForUser($user),
                    "language" => $this->languageService->getLanguageForUser($user),
                    "isSaas"   => true === $isSaas
                ],
                "user"       => $user,
                'derivation' => base64_encode($this->derivationService->derive($user->getPassword())),
                'key'        => base64_encode($key->getSecret())
            ],
            statusCode: IResponse::OK,
            headers: [
                VerificationService::FIELD_NAME_TOKEN     => $token->getValue(),
                VerificationService::FIELD_NAME_USER_HASH => $user->getHash()
            ]
        );

    }

}

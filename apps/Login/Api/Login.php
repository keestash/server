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

use doganoo\PHPUtil\HTTP\Code;
use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\LoginResponse;
use Keestash\App\Helper;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\User\UserService;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\ILogger\ILogger;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\L10N\IL10N;

class Login extends AbstractApi {

    private const DEFAULT_USER_LIFETIME = 60 * 60;

    private IUserRepository    $userRepository;
    private IL10N              $translator;
    private UserService        $userService;
    private ITokenRepository   $tokenManager;
    private TokenService       $tokenService;
    private PersistenceService $persistenceService;
    private ConfigService      $configService;
    private ILocaleService     $localeService;
    private ILanguageService   $languageService;
    private ILogger            $logger;

    public function __construct(
        IUserRepository $userRepository
        , IL10N $translator
        , UserService $userService
        , ITokenRepository $tokenManager
        , TokenService $tokenService
        , PersistenceService $persistenceService
        , ConfigService $configService
        , ILocaleService $localeService
        , ILanguageService $languageService
        , ILogger $logger
        , ?IToken $token = null
    ) {
        $this->userRepository     = $userRepository;
        $this->translator         = $translator;
        $this->userService        = $userService;
        $this->tokenManager       = $tokenManager;
        $this->tokenService       = $tokenService;
        $this->persistenceService = $persistenceService;
        $this->configService      = $configService;
        $this->localeService      = $localeService;
        $this->languageService    = $languageService;
        $this->logger             = $logger;

        parent::__construct($translator, $token);
    }

    public function onCreate(array $parameters): void {

    }

    public function create(): void {
        $userName = $this->getParameter("user", "");
        $password = $this->getParameter("password", "");

        $user = $this->userRepository->getUser($userName);

        $this->logger->debug("user is null: " . (null === $user));
        $response = new LoginResponse();
        $response->setCode(Code::OK);

        if (true === $this->userService->isDisabled($user)) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK,
                [
                    "message" => $this->translator->translate("No User Found")
                ]
            );
        } else if (false === $this->userService->validatePassword($password, $user->getPassword())) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK,
                [
                    "message" => $this->translator->translate("Invalid Credentials")
                ]
            );
        } else {
            $token = $this->tokenService->generate("login", $user);

            $response->addMessage(
                IResponse::RESPONSE_CODE_OK
                , [
                    "message"    => $this->translator->translate("Ok")
                    , "routeTo"  => Helper::getDefaultRoute()
                    , "settings" => [
                        "locale"     => $this->localeService->getLocaleForUser($user)
                        , "language" => $this->languageService->getLanguageForUser($user)
                    ]
                ]
            );

            $response->addHeader(
                "api_token"
                , $token->getValue()
            );

            $response->addHeader(
                'user_hash'
                , $user->getHash()
            );

            $this->tokenManager->add($token);

            $expireTs = DateTimeUtil::getUnixTimestamp() +
                $this->configService->getValue(
                    "user_lifetime"
                    , (string) Login::DEFAULT_USER_LIFETIME
                );
            $this->persistenceService->setPersistenceValue(
                "user_id"
                , (string) $user->getId()
                , (int) $expireTs
            );

        }

        parent::setResponse(
            $response
        );

    }

    public function afterCreate(): void {

    }

}

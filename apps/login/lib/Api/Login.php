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

use doganoo\PHPUtil\Util\DateTimeUtil;
use Keestash\Api\AbstractApi;
use Keestash\Api\Response\LoginResponse;
use Keestash\App\Helper;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Core\Service\User\UserService;
use KSA\Login\Application\Application;
use KSA\Login\Service\TokenService;
use KSP\Api\IResponse;
use KSP\Core\DTO\IToken;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Core\Language\ILanguageService;
use KSP\Core\Service\Core\Locale\ILocaleService;
use KSP\L10N\IL10N;

class Login extends AbstractApi {

    private const DEFAULT_USER_LIFETIME = 60 * 60;

    /** @var IUserRepository|null $userRepository */
    private $userRepository;
    /** @var IL10N|null $translator */
    private $translator;
    /** @var null|UserService $userService */
    private $userService;
    /** @var null|ITokenRepository $tokenManager */
    private $tokenManager;
    /** @var null|TokenService $tokenService */
    private $tokenService;
    /** @var null|IPermissionRepository $permissionManager */
    private $permissionManager;
    /** @var PersistenceService $persistenceService */
    private $persistenceService;
    /** @var ConfigService $configService */
    private $configService;
    /** @var ILocaleService */
    private $localeService;
    /** @var ILanguageService */
    private $languageService;

    public function __construct(
        IUserRepository $userRepository
        , IL10N $translator
        , UserService $userService
        , ITokenRepository $tokenManager
        , TokenService $tokenService
        , IPermissionRepository $permissionManager
        , PersistenceService $persistenceService
        , ConfigService $configService
        , ILocaleService $localeService
        , ILanguageService $languageService
        , ?IToken $token = null
    ) {
        $this->userRepository     = $userRepository;
        $this->translator         = $translator;
        $this->userService        = $userService;
        $this->tokenManager       = $tokenManager;
        $this->tokenService       = $tokenService;
        $this->permissionManager  = $permissionManager;
        $this->persistenceService = $persistenceService;
        $this->configService      = $configService;
        $this->localeService      = $localeService;
        $this->languageService    = $languageService;

        parent::__construct($translator, $token);
    }

    public function onCreate(array $parameters): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_LOGIN_SUBMIT)
        );
    }

    public function create(): void {
        $userName = $this->getParameter("user", "");
        $password = $this->getParameter("password", "");

        $user = $this->userRepository->getUser($userName);

        $response = new LoginResponse();
        $response->setCode(HTTP::OK);

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

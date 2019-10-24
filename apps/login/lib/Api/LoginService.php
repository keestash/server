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

use Keestash\Api\AbstractApi;
use Keestash\Api\Response\LoginResponse;
use Keestash\App\Helper;
use Keestash\Core\DTO\HTTP;
use Keestash\Core\Manager\SessionManager\SessionManager;
use Keestash\Core\Manager\SessionManager\UserSessionManager;
use Keestash\Core\Service\TokenService;
use Keestash\Core\Service\UserService;
use KSA\Login\Application\Application;
use KSP\Api\IResponse;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class LoginService extends AbstractApi {

    /** @var IUserRepository|null $userManager */
    private $userManager = null;
    /** @var null|array $params */
    private $params = null;
    /** @var IL10N|null $translator */
    private $translator = null;
    /** @var null|UserService $userService */
    private $userService = null;
    /** @var null|SessionManager $sessionManager */
    private $sessionManager = null;
    /** @var null|ITokenRepository $tokenManager */
    private $tokenManager = null;
    /** @var null|TokenService $tokenService */
    private $tokenService = null;
    /** @var null|IPermissionRepository $permissionManager */
    private $permissionManager = null;

    public function __construct(
        IUserRepository $userManager
        , IL10N $translator
        , UserService $userService
        , UserSessionManager $sessionManager
        , ITokenRepository $tokenManager
        , TokenService $tokenService
        , IPermissionRepository $permissionManager
    ) {
        $this->userManager       = $userManager;
        $this->translator        = $translator;
        $this->userService       = $userService;
        $this->sessionManager    = $sessionManager;
        $this->tokenManager      = $tokenManager;
        $this->tokenService      = $tokenService;
        $this->permissionManager = $permissionManager;

        parent::__construct($translator);
    }

    public function onCreate(...$params): void {
        $this->params = $params;
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_LOGIN_SUBMIT)
        );
    }

    public function create(): void {
        $userName = $this->params[0] ?? "";
        $password = $this->params[1] ?? "";

        $user = $this->userManager->getUser($userName);

        $response = new LoginResponse();
        $response->setCode(HTTP::OK);

        if (null === $user) {
            $response->addMessage(
                IResponse::RESPONSE_CODE_NOT_OK,
                [
                    "message" => $this->translator->translate("No User Found")
                ]
            );
        } else {
            if (!$this->userService->validatePassword($password, $user->getPassword())) {
                $response->addMessage(
                    IResponse::RESPONSE_CODE_NOT_OK,
                    [
                        "message" => $this->translator->translate("Invalid Credentials")
                    ]
                );
            } else {
                $token = $this->tokenService->generate("login", $user);

                $response->addMessage(
                    IResponse::RESPONSE_CODE_OK,
                    [
                        "message"   => $this->translator->translate("Ok")
                        , "routeTo" => Helper::getDefaultRoute()
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
                $this->sessionManager->setId($user->getId());
                $this->sessionManager->updateTimestamp();
            }
        }

        parent::setResponse(
            $response
        );

    }

    public function afterCreate(): void {

    }

}
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

namespace KSA\Login\Controller;

use Keestash;
use KSA\Login\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class LoginController extends StaticAppController {

    public const TEMPLATE_NAME_LOGIN = "login.twig";

    private $templateManager   = null;
    private $translator        = null;
    private $permissionManager = null;
    private $loader            = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionManager
        , ILoader $loader
    ) {
        $this->templateManager   = $templateManager;
        $this->translator        = $translator;
        $this->permissionManager = $permissionManager;
        $this->loader            = $loader;

        parent::__construct(
            $templateManager
            , $translator
        );
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_LOGIN)
        );
    }

    public function create(): void {

        $this->templateManager->replace(
            LoginController::TEMPLATE_NAME_LOGIN
            , [
                "signIn"                => $this->translator->translate("Sign In")
                , "passwordPlaceholder" => $this->translator->translate("Password")
                , "passwordLabel"       => $this->translator->translate("Password")
                , "userPlaceholder"     => $this->translator->translate("Username")
                , "usernameLabel"       => $this->translator->translate("Username")
                , "text"                => $this->translator->translate("Create New Account")
                , "forgotPassword"      => $this->translator->translate("Forgot your password?")
                , "invalidCredentials"  => $this->translator->translate("Please enter valid credentials")
                , "newAccountLink"      => Keestash::getBaseURL(true) . "/register"
                , "forgotPasswordLink"  => Keestash::getBaseURL(true) . "/forgot_password"
                , "logoPath"            => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "registeringEnabled"  => $this->loader->hasApp(Application::APP_NAME_REGISTER)
            ]
        );

        $string = $this->templateManager
            ->render(LoginController::TEMPLATE_NAME_LOGIN);
        $this->templateManager->replace("app-content.html",
            ["appContent" => $string]
        );
    }

    public function afterCreate(): void {

    }

}
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

namespace KSA\ForgotPassword\Controller;

use Keestash;
use KSA\ForgotPassword\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class ForgotPassword extends StaticAppController {

    private $templateManager   = null;
    private $translator        = null;
    private $permissionManager = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionManager
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->templateManager   = $templateManager;
        $this->translator        = $translator;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_FORGOT_PASSWORD)
        );
    }

    public function create(): void {
        $this->templateManager->replace(
            "forgot_password.html",
            [
                "reset"                           => $this->translator->translate("Reset")
                , "usernameOrPasswordLabel"       => $this->translator->translate("Username or Email Address")
                , "usernameOrPasswordPlaceholder" => $this->translator->translate("Username or Email Address")
                , "forgotPassword"                => $this->translator->translate("Forgot your password?")
                , "invalidCredentials"            => $this->translator->translate("Please enter valid credentials")
                , "newAccountLink"                => Keestash::getBaseURL(true) . "/" . Keestash\Core\App\Register\Application\Application::REGISTER
                , "forgotPasswordLink"            => Keestash::getBaseURL(true) . "/" . Application::FORGOT_PASSWORD
                , "logoPath"                      => Keestash::getBaseURL(false) . "/asset/img/logo.png"
                , "backToLogin"                   => $this->translator->translate("Back To Login")
                , "backToLoginLink"               => Keestash::getBaseURL(true) . "/" . Keestash\Core\App\Login\Application\Application::LOGIN
            ]
        );

        $string = $this->templateManager
            ->render("forgot_password.html");
        $this->templateManager->replace("app-content.html",
            ["appContent" => $string]
        );
    }

    public function afterCreate(): void {

    }

}
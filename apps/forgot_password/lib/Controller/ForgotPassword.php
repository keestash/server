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
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Legacy\Legacy;
use KSA\ForgotPassword\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\L10N\IL10N;

class ForgotPassword extends StaticAppController {

    public const TEMPLATE_NAME = "forgot_password.twig";

    private ITemplateManager      $templateManager;
    private IL10N                 $translator;
    private IPermissionRepository $permissionManager;
    private Legacy                $legacy;
    private IUserStateRepository  $userStateRepository;
    private ILoader               $loader;
    private ConfigService         $configService;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionRepository
        , Legacy $legacy
        , IUserStateRepository $userStateRepository
        , ILoader $loader
        , ConfigService $configService
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->templateManager     = $templateManager;
        $this->translator          = $translator;
        $this->permissionManager   = $permissionRepository;
        $this->legacy              = $legacy;
        $this->userStateRepository = $userStateRepository;
        $this->loader              = $loader;
        $this->configService       = $configService;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_FORGOT_PASSWORD)
        );
    }

    public function create(): void {
        $token = $this->getParameter("token", null);

        $this->getTemplateManager()->replace(
            ForgotPassword::TEMPLATE_NAME
            , [
                // strings
                "resetPassword"                   => $this->getL10N()->translate("Reset")
                , "usernameOrPasswordPlaceholder" => $this->getL10N()->translate("Username or Email Address")
                , "createNewAccountText"          => $this->getL10N()->translate("Not registered Yet?")
                , "createNewAccountActionText"    => $this->getL10N()->translate("Sign Up")
                , "loginToApp"                    => $this->getL10N()->translate("Reset password")
                , "backToLogin"                   => $this->getL10N()->translate("Back To Login")

                // values
                , "backgroundPath"                => Keestash::getBaseURL(false) . "/asset/img/forgot-password-background.jpg"
                , "logoPath"                      => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "backToLoginLink"               => Keestash::getBaseURL(true) . "/" . \KSA\Login\Application\Application::LOGIN
                , "newAccountLink"                => Keestash::getBaseURL(true) . "/register"
                , "forgotPasswordLink"            => Keestash::getBaseURL(true) . "/forgot_password"
                , "registeringEnabled"            => $this->loader->hasApp(\KSA\Login\Application\Application::APP_NAME_REGISTER)
                , "newTab"                        => false === $this->configService->getValue('debug', false)
            ]
        );

        $string = $this->getTemplateManager()
            ->render(ForgotPassword::TEMPLATE_NAME);

        $this->getTemplateManager()->replace(
            ITemplate::APP_CONTENT
            , [
                "appContent" => $string
            ]
        );
    }

    public function afterCreate(): void {

    }

}

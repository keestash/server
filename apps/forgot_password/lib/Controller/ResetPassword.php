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
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class ResetPassword extends StaticAppController {

    private $parameters        = null;
    private $templateManager   = null;
    private $translator        = null;
    private $permissionManager = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $il10n
        , IPermissionRepository $permissionRepository
    ) {
        parent::__construct(
            $templateManager
            , $il10n
        );

        $this->templateManager   = $templateManager;
        $this->translator        = $il10n;
        $this->permissionManager = $permissionRepository;
    }

    public function onCreate(...$params): void {
        $this->parameters = $params;
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_RESET_PASSWORD)
        );
    }

    public function create(): void {
        $rendered = null;
        $token    = $this->parameters[0] ?? null;
        if (null === $token) {
            $rendered = "no request found";
        }
        $dbToken = $token; // TODO ask database

        if ($token !== $dbToken) {
            $rendered = "no request found";
        }

        $this->templateManager->replace(
            "reset_password.html",
            [
                "reset"                          => $this->translator->translate("Reset")
                , "newPasswordLabel"             => $this->translator->translate("New Password")
                , "newPasswordRepeaKSAbel"       => $this->translator->translate("New Password Repeat")
                , "userNameLabel"                => $this->translator->translate("Username")
                , "newPasswordPlaceholder"       => $this->translator->translate("New Password Repeat")
                , "newPasswordRepeatPlaceholder" => $this->translator->translate("New Password")
                , "usernamePlaceholder"          => $this->translator->translate("Username")
                , "logoPath"                     => Keestash::getBaseURL(false) . "/asset/img/logo.png"
                , "backToLogin"                  => $this->translator->translate("Back To Login")
                , "backToLoginLink"              => Keestash::getBaseURL(true) . "/" . \KSA\Login\Application\Application::LOGIN
            ]
        );

        $string = $this->templateManager
            ->render("reset_password.html");
        $this->templateManager->replace(
            ITemplate::APP_CONTENT
            , ["appContent" => $string]
        );


    }

    public function afterCreate(): void {

    }

}

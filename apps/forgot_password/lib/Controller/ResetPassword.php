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

    private const RESET_PASSWORD_TEMPLATE_NAME = "reset_password.twig";

    /** @var IPermissionRepository */
    private $permissionManager;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $il10n
        , IPermissionRepository $permissionRepository
    ) {
        parent::__construct(
            $templateManager
            , $il10n
        );

        $this->permissionManager = $permissionRepository;
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            $this->permissionManager->getPermission(Application::PERMISSION_RESET_PASSWORD)
        );
    }

    public function create(): void {
        $rendered = null;
        $token    = $this->getParameter("token", null);

        if (null === $token) {
            $rendered = "no request found";
        }
        $dbToken = $token; // TODO ask database

        if ($token !== $dbToken) {
            $rendered = "no request found";
        }

        $this->getTemplateManager()->replace(
            ResetPassword::RESET_PASSWORD_TEMPLATE_NAME
            , [
                // strings
                "reset"                          => $this->getL10n()->translate("Reset")
                , "newPasswordLabel"             => $this->getL10n()->translate("New Password")
                , "newPasswordRepeaKSAbel"       => $this->getL10n()->translate("New Password Repeat")
                , "userNameLabel"                => $this->getL10n()->translate("Username")
                , "newPasswordPlaceholder"       => $this->getL10n()->translate("New Password Repeat")
                , "newPasswordRepeatPlaceholder" => $this->getL10n()->translate("New Password")
                , "usernamePlaceholder"          => $this->getL10n()->translate("Username")
                , "backToLogin"                  => $this->getL10n()->translate("Back To Login")

                // values
                , "logoPath"                     => Keestash::getBaseURL(false) . "/asset/img/logo.png"
                , "backToLoginLink"              => Keestash::getBaseURL(true) . "/" . \KSA\Login\Application\Application::LOGIN
            ]
        );

        $string = $this->getTemplateManager()
            ->render(ResetPassword::RESET_PASSWORD_TEMPLATE_NAME);
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

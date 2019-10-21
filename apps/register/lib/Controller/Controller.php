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

namespace KSA\Register\Controller;

use Keestash;
use KSA\Register\Application\Application;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends StaticAppController {

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
            $this->permissionManager->getPermission(Application::PERMISSION_REGISTER)
        );
    }

    public function create(): void {
        $this->templateManager->replace(
            "register.html"
            , [
            "firstNameLabel"                => $this->translator->translate("First Name")
            , "firstNamePlaceholder"        => $this->translator->translate("First Name")
            , "lastNameLabel"               => $this->translator->translate("Last Name")
            , "lastNamePlaceholder"         => $this->translator->translate("Last Name")
            , "userNameLabel"               => $this->translator->translate("Username")
            , "emailLabel"                  => $this->translator->translate("Email")
            , "userNamePlaceholder"         => $this->translator->translate("Username")
            , "emailPlaceholder"            => $this->translator->translate("Email")
            , "emailStillAvailable"         => $this->translator->translate("The Email Address is still available")
            , "emailTaken"                  => $this->translator->translate("The Email Address is already in use")
            , "passwordLabel"               => $this->translator->translate("Password")
            , "passwordRepeaKSAbel"         => $this->translator->translate("Repeat Password")
            , "termsConditionsFirstPart"    => $this->translator->translate("I agree to the")
            , "termsAndConditions"          => $this->translator->translate("Terms and Conditions")
            , "submit"                      => $this->translator->translate("Register")
            , "passwordPlaceholder"         => $this->translator->translate("Password")
            , "passwordRepeatPlaceholder"   => $this->translator->translate("Repat Password")
            , "passwordInvalidText"         => $this->translator->translate("Your password has not reached the minimum requirements")
            , "emailInvalidText"            => $this->translator->translate("Your email address seems to be invalid")
            , "passwordRepeatInvalidText"   => $this->translator->translate("Your passwords do not match")
            , "minimumPasswordRequirements" => $this->translator->translate("Your password has to contain at least one upper case, one lower case, one digit and 6 characters")
            , "createNewAccount"            => $this->translator->translate("Create New Account")
            , "createNewAccountDesc"        => $this->translator->translate("Sign Up for Keestash, the Open Source Password Safe")
            , "backToLogin"                 => $this->translator->translate("Back To Login")
            , "termsConditionsLink"         => Keestash::getBaseURL(true) . "/" . \KSA\TNC\Application\Application::PERMISSION_TNC
            , "backToLoginLink"             => Keestash::getBaseURL(true) . "/" . \KSA\Login\Application\Application::LOGIN
            , "logoPath"                    => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
        ]);

        parent::setAppContent($this->templateManager->render("register.html"));
    }

    public function afterCreate(): void {

    }

}
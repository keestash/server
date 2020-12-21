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
use Keestash\Core\Service\User\UserService;
use KSA\Register\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends StaticAppController {

    public const TEMPLATE_NAME_REGISTER             = "register.twig";
    public const TEMPLATE_NAME_REGISTER_NOT_ENABLED = "register_not_enabled.twig";

    private ITemplateManager      $templateManager;
    private IL10N                 $translator;
    private IPermissionRepository $permissionRepository;
    private ILoader               $loader;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionRepository
        , ILoader $loader
    ) {
        parent::__construct(
            $templateManager
            , $translator
        );

        $this->templateManager      = $templateManager;
        $this->translator           = $translator;
        $this->permissionRepository = $permissionRepository;
        $this->loader               = $loader;

    }

    public function onCreate(...$params): void {
        $this->setPermission(
            $this->permissionRepository->getPermission(Application::PERMISSION_REGISTER)
        );
    }

    public function create(): void {
        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp(Application::APP_NAME_REGISTER);

        if (true === $registerEnabled) {
            $this->parseRegularRegister();
        } else {
            $this->parseRegisterNotEnabled();
        }

    }

    private function parseRegularRegister(): void {
        $this->templateManager->replace(
            Controller::TEMPLATE_NAME_REGISTER
            , [
            // first name
            "firstNameLabel"                => $this->translator->translate("First Name")
            , "firstNamePlaceholder"        => $this->translator->translate("First Name")
            , "firstNameInvalidText"        => $this->translator->translate("Please provide a first name")

            // last name
            , "lastNameLabel"               => $this->translator->translate("Last Name")
            , "lastNamePlaceholder"         => $this->translator->translate("Last Name")
            , "lastNameInvalidText"         => $this->translator->translate("Please provide a last name")

            // user name
            , "userNameLabel"               => $this->translator->translate("Username")
            , "userNamePlaceholder"         => $this->translator->translate("Username")
            , "userNameInvalidText"         => $this->translator->translate("The username is invalid or taken")

            // email
            , "emailLabel"                  => $this->translator->translate("Email")
            , "emailPlaceholder"            => $this->translator->translate("Email")
            , "emailStillAvailable"         => $this->translator->translate("The Email Address is still available")
            , "emailTaken"                  => $this->translator->translate("The Email Address is already in use")

            // phone
            , "phoneLabel"                  => $this->translator->translate("Phone")
            , "phonePlaceholder"            => $this->translator->translate("Phone")
            , "phoneInvalidText"            => $this->translator->translate("The phone number seems to be incorrect")

            // website
            , "websiteLabel"                => $this->translator->translate("Website")
            , "websitePlaceholder"          => $this->translator->translate("Website")
            , "websiteInvalidText"          => $this->translator->translate("The provided URL seems to be incorrect")

            // password
            , "passwordLabel"               => $this->translator->translate("Password")
            , "passwordRepeaKSAbel"         => $this->translator->translate("Repeat Password")

            // terms and conditions
            , "termsConditionsFirstPart"    => $this->translator->translate("I agree to the")
            , "termsAndConditions"          => $this->translator->translate("Terms and Conditions")

            // stuff
            , "submit"                      => $this->translator->translate("Register")
            , "passwordPlaceholder"         => $this->translator->translate("Password")
            , "passwordRepeatPlaceholder"   => $this->translator->translate("Repat Password")
            , "passwordInvalidText"         => $this->translator->translate("Your password has not reached the minimum requirements")
            , "emailInvalidText"            => $this->translator->translate("Your email address seems to be invalid")
            , "passwordRepeatInvalidText"   => $this->translator->translate("Your passwords do not match")
            , "minimumPasswordRequirements" => $this->translator->translate("Your password has to contain at least one upper case, one lower case, one digit and " . UserService::MINIMUM_NUMBER_OF_CHARACTERS_FOR_USER_PASSWORD . " characters")
            , "createNewAccount"            => $this->translator->translate("Create New Account")
            , "createNewAccountDesc"        => $this->translator->translate("Sign Up for Keestash, the Open Source Password Safe")
            , "backToLogin"                 => $this->translator->translate("Back To Login")

            // values
            , "backgroundPath"              => Keestash::getBaseURL(false) . "/asset/img/login-background.jpg"
            , "logoPath"                    => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"

            , "termsConditionsLink"         => Keestash::getBaseURL(true) . "/" . \KSA\TNC\Application\Application::TERMS_AND_CONDITIONS
            , "backToLoginLink"             => Keestash::getBaseURL(true) . "/" . \KSA\Login\Application\Application::LOGIN
        ]);
        $this->setAppContent(
            $this->templateManager->render(Controller::TEMPLATE_NAME_REGISTER)
        );
    }

    private function parseRegisterNotEnabled(): void {
        $this->templateManager->replace(
            Controller::TEMPLATE_NAME_REGISTER_NOT_ENABLED
            , []
        );
        $this->setAppContent(
            $this->templateManager->render(Controller::TEMPLATE_NAME_REGISTER_NOT_ENABLED)
        );
    }

    public function afterCreate(): void {

    }

}

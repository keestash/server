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

use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\HTTPService;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class Controller extends StaticAppController {

    public const TEMPLATE_NAME_REGISTER             = "register.twig";
    public const TEMPLATE_NAME_REGISTER_NOT_ENABLED = "register_not_enabled.twig";

    private IL10N                     $translator;
    private ILoader                   $loader;
    private ConfigService             $configService;
    private TemplateRendererInterface $templateRenderer;
    private HTTPService               $httpService;

    public function __construct(
        IL10N $translator
        , ILoader $loader
        , ConfigService $configService
        , IAppRenderer $appRenderer
        , TemplateRendererInterface $templateRenderer
        , HTTPService $httpService
    ) {
        parent::__construct($appRenderer);

        $this->translator       = $translator;
        $this->loader           = $loader;
        $this->configService    = $configService;
        $this->templateRenderer = $templateRenderer;
        $this->httpService      = $httpService;
    }

    public function run(ServerRequestInterface $request): string {
        // a little bit out of sense, but
        // we do not want to enable registering
        // even if someone has found a hacky way
        // to enable this controller!
        $registerEnabled = $this->loader->hasApp('register');

        if (true === $registerEnabled) {
            return $this->parseRegularRegister();
        }
        return $this->parseRegisterNotEnabled();
    }

    private function parseRegularRegister(): string {
        return $this->templateRenderer
            ->render(
                'register::register'
                , [
                // first name
                "firstNameLabel"              => $this->translator->translate("First name")
                , "firstNamePlaceholder"      => $this->translator->translate("First name")

                // last name
                , "lastNameLabel"             => $this->translator->translate("Last Name")
                , "lastNamePlaceholder"       => $this->translator->translate("Last Name")

                // user name
                , "userNameLabel"             => $this->translator->translate("Username")
                , "userNamePlaceholder"       => $this->translator->translate("Username")

                // email
                , "emailLabel"                => $this->translator->translate("Email")
                , "emailPlaceholder"          => $this->translator->translate("Email")

                // phone
                , "phoneLabel"                => $this->translator->translate("Phone")
                , "phonePlaceholder"          => $this->translator->translate("Phone")

                // website
                , "websiteLabel"              => $this->translator->translate("Website")
                , "websitePlaceholder"        => $this->translator->translate("Website")

                // password
                , "passwordLabel"             => $this->translator->translate("Password")
                , "passwordRepeatLabel"       => $this->translator->translate("Repeat Password")

                // terms and conditions
                , "termsConditionsFirstPart"  => $this->translator->translate("I agree to the")
                , "termsAndConditions"        => $this->translator->translate("Terms and Conditions")

                // stuff
                , "submit"                    => $this->translator->translate("Register")
                , "passwordPlaceholder"       => $this->translator->translate("Password")
                , "passwordRepeatPlaceholder" => $this->translator->translate("Repat Password")
                , "createNewAccount"          => $this->translator->translate("Create New Account")
                , "createNewAccountDesc"      => $this->translator->translate("Sign Up for Keestash, the Open Source Password Safe")
                , "backToLogin"               => $this->translator->translate("Log In")
                , "backToLoginQuestion"       => $this->translator->translate("Have an account?")

                // values
                , "backgroundPath"            => $this->httpService->getBaseURL(false) . "/asset/img/register-background.jpg"
                , "logoPath"                  => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted_no_background.png"

                , "termsConditionsLink"       => $this->httpService->getBaseURL(true) . "/tnc"
                , "backToLoginLink"           => $this->httpService->getBaseURL(true) . "/login"
                , "newTab"                    => false === $this->configService->getValue('debug', false)
            ]);
    }

    private function parseRegisterNotEnabled(): string {
        return $this->templateRenderer
            ->render(
                'register::register_not_enabled'
                , []
            );
    }

}

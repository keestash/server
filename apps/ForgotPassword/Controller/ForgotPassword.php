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

use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\HTTPService;
use KSA\Login\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Service\Controller\IAppRenderer;
use KSP\L10N\IL10N;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ServerRequestInterface;

class ForgotPassword extends StaticAppController {

    private IL10N                     $translator;
    private ILoader                   $loader;
    private ConfigService             $configService;
    private HTTPService               $httpService;
    private TemplateRendererInterface $templateRenderer;

    public function __construct(
        IL10N $translator
        , ILoader $loader
        , ConfigService $configService
        , IAppRenderer $renderer
        , HTTPService $httpService
        , TemplateRendererInterface $templateRenderer
    ) {
        parent::__construct($renderer);

        $this->translator       = $translator;
        $this->loader           = $loader;
        $this->configService    = $configService;
        $this->httpService      = $httpService;
        $this->templateRenderer = $templateRenderer;
    }

    public function run(ServerRequestInterface $request): string {
        $token = $request->getAttribute("token");

        return $this->templateRenderer
            ->render(
                'forgotPassword::forgot_password'
                , [
                // strings
                "resetPassword"                   => $this->translator->translate("Reset")
                , "usernameOrPasswordPlaceholder" => $this->translator->translate("Username or Email Address")
                , "createNewAccountText"          => $this->translator->translate("Not registered Yet?")
                , "createNewAccountActionText"    => $this->translator->translate("Sign Up")
                , "loginToApp"                    => $this->translator->translate("Reset password")
                , "backToLogin"                   => $this->translator->translate("Back To Login")

                // values
                , "backgroundPath"                => $this->httpService->getBaseURL(false) . "/asset/img/forgot-password-background.jpg"
                , "logoPath"                      => $this->httpService->getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "backToLoginLink"               => $this->httpService->getBaseURL(true) . "/" . Application::LOGIN
                , "newAccountLink"                => $this->httpService->getBaseURL(true) . "/register"
                , "forgotPasswordLink"            => $this->httpService->getBaseURL(true) . "/forgot_password"
                , "registeringEnabled"            => $this->loader->hasApp(Application::APP_NAME_REGISTER)
                , "forgotPasswordEnabled"         => $this->loader->hasApp(Application::APP_NAME_FORGOT_PASSWORD)
                , "newTab"                        => false === $this->configService->getValue('debug', false)
            ]);

    }

}

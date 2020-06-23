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
use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Legacy\Legacy;
use KSA\Login\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class LoginController extends StaticAppController {

    public const TEMPLATE_NAME_LOGIN = "login.twig";

    private $templateManager = null;
    private $translator      = null;
    /** @var IPermissionRepository */
    private $permissionRepository = null;
    private $loader               = null;
    private $persistenceService   = null;
    private $legacy               = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionRepository
        , ILoader $loader
        , PersistenceService $persistenceService
        , Legacy $legacy
    ) {
        $this->templateManager      = $templateManager;
        $this->translator           = $translator;
        $this->permissionRepository = $permissionRepository;
        $this->loader               = $loader;
        $this->persistenceService   = $persistenceService;
        $this->legacy               = $legacy;

        parent::__construct(
            $templateManager
            , $translator
        );
    }

    public function onCreate(...$params): void {
        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {

        $userId = $this->persistenceService->getValue("user_id", null);
        $hashes = Keestash::getServer()->getUserHashes();

        if (null !== $userId && $hashes->containsValue((int) $userId)) {
            Keestash::getServer()->getHTTPRouter()->routeTo(
                Keestash::getServer()->getAppLoader()->getDefaultApp()->getBaseRoute()
            );
            exit();
        }
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
                , "loginToApp"          => $this->translator->translate("Login to {$this->legacy->getApplication()->get('name')}")
                , "newAccountLink"      => Keestash::getBaseURL(true) . "/register"
                , "forgotPasswordLink"  => Keestash::getBaseURL(true) . "/forgot_password"
                , "logoPath"            => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "registeringEnabled"  => $this->loader->hasApp(Application::APP_NAME_REGISTER)
            ]
        );

        $string = $this->templateManager
            ->render(LoginController::TEMPLATE_NAME_LOGIN);
        $this->templateManager->replace(
            ITemplate::APP_CONTENT
            , ["appContent" => $string]
        );
    }

    public function afterCreate(): void {

    }

}

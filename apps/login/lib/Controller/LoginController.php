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

    /** @var IPermissionRepository */
    private $permissionRepository;
    /** @var ILoader */
    private $loader;
    /** @var PersistenceService */
    private $persistenceService;
    /** @var Legacy */
    private $legacy;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionRepository
        , ILoader $loader
        , PersistenceService $persistenceService
        , Legacy $legacy
    ) {
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
        $this->getTemplateManager()->replace(
            LoginController::TEMPLATE_NAME_LOGIN
            , [
                "signIn"                => $this->getL10N()->translate("Sign In")
                , "passwordPlaceholder" => $this->getL10N()->translate("Password")
                , "passwordLabel"       => $this->getL10N()->translate("Password")
                , "userPlaceholder"     => $this->getL10N()->translate("Username")
                , "usernameLabel"       => $this->getL10N()->translate("Username")
                , "text"                => $this->getL10N()->translate("Create New Account")
                , "forgotPassword"      => $this->getL10N()->translate("Forgot your password?")
                , "invalidCredentials"  => $this->getL10N()->translate("Please enter valid credentials")
                , "loginToApp"          => $this->getL10N()->translate("Login to {$this->legacy->getApplication()->get('name')}")
                , "newAccountLink"      => Keestash::getBaseURL(true) . "/register"
                , "forgotPasswordLink"  => Keestash::getBaseURL(true) . "/forgot_password"
                , "logoPath"            => Keestash::getBaseURL(false) . "/asset/img/logo_inverted.png"
                , "registeringEnabled"  => $this->loader->hasApp(Application::APP_NAME_REGISTER)
            ]
        );

        $string = $this->getTemplateManager()
            ->render(LoginController::TEMPLATE_NAME_LOGIN);
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

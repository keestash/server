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
use Keestash\Core\Manager\CookieManager\CookieManager;
use Keestash\Core\Repository\Instance\InstanceDB;
use Keestash\Core\Service\Config\ConfigService;
use Keestash\Core\Service\HTTP\PersistenceService;
use Keestash\Legacy\Legacy;
use KSA\Login\Application\Application;
use KSP\App\ILoader;
use KSP\Core\Controller\StaticAppController;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Manager\TemplateManager\ITemplate;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class LoginController extends StaticAppController {

    public const TEMPLATE_NAME_LOGIN = "login.twig";

    private IPermissionRepository $permissionRepository;
    private ILoader               $loader;
    private PersistenceService    $persistenceService;
    private Legacy                $legacy;
    private ConfigService         $configService;
    private InstanceDB            $instanceDb;
    private CookieManager         $cookieManager;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $translator
        , IPermissionRepository $permissionRepository
        , ILoader $loader
        , PersistenceService $persistenceService
        , Legacy $legacy
        , ConfigService $configService
        , CookieManager $cookieManager
        , InstanceDB $instanceDB
    ) {
        $this->permissionRepository = $permissionRepository;
        $this->loader               = $loader;
        $this->persistenceService   = $persistenceService;
        $this->legacy               = $legacy;
        $this->configService        = $configService;
        $this->instanceDb           = $instanceDB;
        $this->cookieManager        = $cookieManager;

        parent::__construct(
            $templateManager
            , $translator
        );
    }

    public function onCreate(...$params): void {

    }

    public function create(): void {

        $userId = $this->persistenceService->getValue("user_id");
        $hashes = Keestash::getServer()->getUserHashes();

        if (null !== $userId && $hashes->containsValue((int) $userId)) {
            Keestash::getServer()->getHTTPRouter()->routeTo(
                Keestash::getServer()->getAppLoader()->getDefaultApp()->getBaseRoute()
            );
            exit();
        }

        $isDemoMode = $this->instanceDb->getOption("demo") === "true";
        $demo       = $isDemoMode
            ? md5(uniqid())
            : null;

        $this->getTemplateManager()->replace(
            LoginController::TEMPLATE_NAME_LOGIN
            , [
                // strings
                "signIn"                       => $this->getL10N()->translate("Sign In")
                , "passwordPlaceholder"        => $this->getL10N()->translate("Password")
                , "userNamePlaceholder"        => $this->getL10N()->translate("Username")
                , "createNewAccountText"       => $this->getL10N()->translate("Create New Account")
                , "createNewAccountActionText" => $this->getL10N()->translate("Sign Up")
                , "forgotPasswordText"         => $this->getL10N()->translate("Forgot your password?")
                , "forgotPasswordActionText"   => $this->getL10N()->translate("Request")
                , "loginToApp"                 => $this->getL10N()->translate("Login to {$this->legacy->getApplication()->get('name')}")
                , "newAccountLink"             => Keestash::getBaseURL(true) . "/register"
                , "forgotPasswordLink"         => Keestash::getBaseURL(true) . "/forgot_password"
                , "registeringEnabled"         => $this->loader->hasApp(Application::APP_NAME_REGISTER)

                // values
                , "backgroundPath"             => Keestash::getBaseURL(false) . "/asset/img/login-background.jpg"
                , "logoPath"                   => Keestash::getBaseURL(false) . "/asset/img/logo_inverted_no_background.png"
                , "newTab"                     => false === $this->configService->getValue('debug', false)
                , "demo"                       => $demo
                , "tncLink"                    => Keestash::getBaseURL(true) . "/tnc/"
                , "demoMode"                   => [
                    "isDemoMode"      => $isDemoMode
                    , "sensitiveData" => $this->getL10N()->translate("Please do not input sensitive data as this the instance you are logging in is only for demonstration purposes!")
                    , "deleteInfo"    => $this->getL10N()->translate("The data submitted here will be deleted after 60 minutes.")
                    , "adminUser"     => $this->getL10N()->translate("Username: " . IUser::DEMO_USER_NAME)
                    , "adminPassword" => $this->getL10N()->translate("Password: " . IUser::DEMO_PASSWORD)
                ]
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

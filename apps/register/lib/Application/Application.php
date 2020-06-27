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

namespace KSA\Register\Application;

use Keestash;
use Keestash\Core\Service\EmailService;
use Keestash\Legacy\Legacy;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Controller\Controller;
use KSA\Register\Hook\EmailAfterRegistration;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Application extends Keestash\App\Application {

    public const APP_NAME_REGISTER = "register";

    public const PERMISSION_REGISTER     = "register";
    public const PERMISSION_REGISTER_ADD = "register_add";
    public const REGISTER                = "register";
    public const REGISTER_ADD            = "register/add/";
    public const USER_EXISTS             = "user/exists/{userName}/";

    public function register(): void {

        $this->registerRoute(
            Application::REGISTER
            , Controller::class
        );

        $this->registerApiRoute(
            Application::REGISTER_ADD
            , Add::class
            , [IRouterManager::POST]
        );

        $this->registerPublicApiRoute(
            Application::REGISTER_ADD
        );

        $this->addJavascript(
            Application::REGISTER
        );

        $this->registerApiRoute(
            Application::USER_EXISTS
            , Exists::class
            , [IRouterManager::GET]
        );

        $this->registerPublicApiRoute(
            Application::USER_EXISTS
        );

        $this->registerPublicRoute(Application::REGISTER);

        Keestash::getServer()
            ->getRegistrationHookManager()
            ->addPost(new EmailAfterRegistration(
                Keestash::getServer()->query(ITemplateManager::class)
                , Keestash::getServer()->query(EmailService::class)
                , Keestash::getServer()->query(Legacy::class)
                , Keestash::getServer()->query(IL10N::class)
            ));

    }

}


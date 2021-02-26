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
use Keestash\Core\Service\Email\EmailService;
use Keestash\Core\Service\User\Event\UserCreatedEvent;
use Keestash\Core\Service\User\UserService;
use Keestash\Legacy\Legacy;
use KSA\Register\Api\User\Add;
use KSA\Register\Api\User\Exists;
use KSA\Register\Api\User\MailExists;
use KSA\Register\Command\CreateUser;
use KSA\Register\Controller\Controller;
use KSA\Register\Event\EmailAfterRegistration;
use KSP\Core\Manager\RouterManager\IRouterManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Validation\IValidationService;
use KSP\L10N\IL10N;

class Application extends Keestash\App\Application {

    public const APP_NAME_REGISTER = "register";

    public const PERMISSION_REGISTER     = "register";
    public const PERMISSION_REGISTER_ADD = "register_add";
    public const REGISTER                = "register";
    public const REGISTER_ADD            = "register/add/";
    public const USER_EXISTS             = "user/exists/{userName}/";
    public const MAIL_EXISTS             = "user/mail/exists/{address}/";

    public function register(): void {

        $this->addJavascript(
            Application::REGISTER
        );

        $this->registerServices();
        $this->registerCommands();
        $this->registerListener();
        $this->registerApiRoutes();
        $this->registerRoutes();
    }

    private function registerRoutes(): void {
        $this->registerRoute(
            Application::REGISTER
            , Controller::class
        );
        $this->registerPublicRoute(Application::REGISTER);
    }

    private function registerApiRoutes(): void {
        $this->registerApiRoute(
            Application::REGISTER_ADD
            , Add::class
            , [IRouterManager::POST]
        );

        $this->registerPublicApiRoute(
            Application::REGISTER_ADD
        );

        $this->registerApiRoute(
            Application::USER_EXISTS
            , Exists::class
            , [IRouterManager::GET]
        );

        $this->registerApiRoute(
            Application::MAIL_EXISTS
            , MailExists::class
            , [IRouterManager::GET]
        );
    }

    private function registerListener(): void {
        Keestash::getServer()
            ->getEventManager()
            ->registerListener(
                UserCreatedEvent::class
                , new EmailAfterRegistration(
                    Keestash::getServer()->query(ITemplateManager::class)
                    , Keestash::getServer()->query(EmailService::class)
                    , Keestash::getServer()->query(Legacy::class)
                    , Keestash::getServer()->query(IL10N::class)
                    , Keestash::getServer()->getFileLogger()
                )
            );
    }

    private function registerServices(): void {
        Keestash::getServer()
            ->register(CreateUser::class, function () {
                return new CreateUser(
                    Keestash::getServer()->query(IUserRepository::class)
                    , Keestash::getServer()->query(UserService::class)
                    , Keestash::getServer()->query(IValidationService::class)
                    , Keestash::getServer()->query(IUserStateRepository::class)
                );
            }
            );
    }

    private function registerCommands(): void {
        $this->registerCommand(
            Keestash::getServer()->query(CreateUser::class)
        );
    }

}


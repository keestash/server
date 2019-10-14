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
use Keestash\Core\Manager\RouterManager\RouterManager;
use Keestash\Core\Service\EmailService;
use Keestash\Legacy\Legacy;
use KSA\Register\Api\Add;
use KSA\Register\Controller\Controller;
use KSA\Register\Hook\EmailAfterRegistration;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class Application extends Keestash\App\Application {

    public const PERMISSION_REGISTER     = "register";
    public const PERMISSION_REGISTER_ADD = "register_add";
    public const REGISTER                = "register";
    public const REGISTER_ADD            = "register/add/";

    public function register(): void {

        parent::registerRoute(
            self::REGISTER
            , Controller::class
        );

        parent::registerApiRoute(
            self::REGISTER_ADD
            , Add::class
            , [RouterManager::POST]
        );

        parent::registerPublicApiRoute(
            self::REGISTER_ADD
        );

        parent::addJavascript(
            self::REGISTER
        );

        parent::registerPublicRoute(self::REGISTER);

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


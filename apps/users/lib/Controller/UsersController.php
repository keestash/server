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

namespace KSA\Users\Controller;

use Keestash;
use Keestash\Core\Permission\PermissionFactory;
use Keestash\View\Navigation\Part;
use KSA\Users\Application\Application;
use KSP\Core\Controller\AppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UsersController extends AppController {

    public const ALL_USERS = 1;

    private $id = null;

    private $l10n              = null;
    private $templateManager   = null;
    private $userManager       = null;
    private $permissionManager = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUserRepository $userManager
        , IPermissionRepository $permissionManager
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->id = UsersController::ALL_USERS;

        $this->l10n              = $l10n;
        $this->templateManager   = $templateManager;
        $this->userManager       = $userManager;
        $this->permissionManager = $permissionManager;
    }

    public function onCreate(...$params): void {
        $this->setPermission(
            PermissionFactory::getDefaultPermission()
        );
    }

    public function create(): void {
        $this->addAppNavigation(
            $this->getPart(
                $this->l10n->translate("All")
                , UsersController::ALL_USERS)
        );

        $this->getTemplateManager()->replace(
            Application::TEMPLATE_NAME_ALL_USERS
            , [
                "name"                        => $this->l10n->translate("Name")
                , "firstName"                 => $this->l10n->translate("First Name")
                , "lastName"                  => $this->l10n->translate("Last Name")
                , "hash"                      => $this->l10n->translate("Hash")
                , "email"                     => $this->l10n->translate("Email")
                , "website"                   => $this->l10n->translate("Website")
                , "password"                  => $this->l10n->translate("Password")
                , "addNewPasswordPlaceholder" => $this->l10n->translate("New Password")
                , "phone"                     => $this->l10n->translate("Phone")
                , "registerDate"              => $this->l10n->translate("RegistrationDate")
                , "users"                     => $this->userManager->getAll()
            ]
        );

        $this->setAppContent(
            $this->templateManager->render(Application::TEMPLATE_NAME_ALL_USERS)
        );

    }

    private function getPart(string $name, int $id) {
        $x = new Part();
        $x->setId($id);
        $x->setName($name);
        $x->setColorCode("");
        $x->setHref(Keestash::getBaseURL() . "/" . Application::APP_ID . "/list/" . $id . "/");
        return $x;
    }

    public function afterCreate(): void {

    }

}
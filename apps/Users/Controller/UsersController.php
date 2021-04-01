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


use KSA\Users\Application\Application;
use KSP\Core\Controller\AppController;
use KSP\Core\Manager\TemplateManager\ITemplateManager;

use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class UsersController extends AppController {

    public const ALL_USERS = 1;

    private IL10N                  $l10n;
    private ITemplateManager       $templateManager;
    private ?IUserRepository       $userManager;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUserRepository $userRepository
    ) {
        parent::__construct(
            $templateManager
            , $l10n
        );

        $this->l10n              = $l10n;
        $this->templateManager   = $templateManager;
        $this->userManager       = $userRepository;
    }

    public function onCreate(): void {

    }

    public function create(): void {

        $this->getTemplateManager()->replace(
            "users.twig"
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
                , "delete"                    => $this->l10n->translate("Delete")
                , "lock"                      => $this->l10n->translate("Lock")
                , "lockedInfo"                => $this->l10n->translate("This Account is locked")
                , "deletedInfo"               => $this->l10n->translate("This Account is marked for deletion and will be completly removed from the system once the deadline is reached")
                , "addUser"                   => $this->l10n->translate("Add User")
                , "users"                     => $this->userManager->getAll()
            ]
        );

        $this->setAppContent(
            $this->templateManager->render("users.twig")
        );

    }

    public function afterCreate(): void {

    }

}

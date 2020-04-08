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
use KSA\Users\Application\Application;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\User\IUserRepository;
use KSP\L10N\IL10N;

class AllUsers {

    private $templateManager = null;
    private $l10n            = null;
    private $userManager     = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUserRepository $userManager
    ) {
        $this->templateManager = $templateManager;
        $this->l10n            = $l10n;
        $this->userManager     = $userManager;
    }

    public function handle(): string {

        $this->templateManager->replace(
            Application::TEMPLATE_NAME_ALL_USERS
            , [
                "name"                        => $this->l10n->translate("Name")
                , "firstName"                 => $this->l10n->translate("First Name")
                , "lastName"                  => $this->l10n->translate("Last Name")
                , "email"                     => $this->l10n->translate("Email")
                , "registerDate"              => $this->l10n->translate("RegistrationDate")
                , "users"                     => $this->userManager->getAll()
                , "modalTitle"                => $this->l10n->translate("New User")
                , "header"                    => $this->l10n->translate("Add a New User By Using the Form below")
                , "profileImage"              => Keestash::getBaseURL(false) . "/asset/img/profile-picture.png"
                , "userNameLabel"             => $this->l10n->translate("User Name")
                , "userNamePlaceholder"       => $this->l10n->translate("User Name")
                , "firstNameLabel"            => $this->l10n->translate("First Name")
                , "firstNamePlaceholder"      => $this->l10n->translate("First Name")
                , "lastNameLabel"             => $this->l10n->translate("Last Name")
                , "lastNamePlaceholder"       => $this->l10n->translate("Last Name")
                , "emailLabel"                => $this->l10n->translate("Email")
                , "emailPlaceholder"          => $this->l10n->translate("Email")
                , "phoneLabel"                => $this->l10n->translate("Phone")
                , "phonePlaceholder"          => $this->l10n->translate("Phone")
                , "passwordLabel"             => $this->l10n->translate("Login")
                , "passwordPlaceholder"       => $this->l10n->translate("Login")
                , "passwordRepeaKSAbel"       => $this->l10n->translate("Login Repeat")
                , "passwordRepeatPlaceholder" => $this->l10n->translate("Login Repeat")
                , "websiteLabel"              => $this->l10n->translate("Website")
                , "websitePlaceholder"        => $this->l10n->translate("Website")
                , "imageDescription"          => $this->l10n->translate("Change your profile image in the settings menu")
                , "cancel"                    => $this->l10n->translate("Cancel")
                , "addUser"                   => $this->l10n->translate("Add User")
            ]
        );

        return $this->templateManager->render(Application::TEMPLATE_NAME_ALL_USERS);

    }

}
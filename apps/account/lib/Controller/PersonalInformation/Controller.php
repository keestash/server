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

namespace KSA\Account\Controller\PersonalInformation;

use Keestash\Core\Permission\PermissionFactory;
use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\UserService;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\L10N\IL10N;

class Controller extends \KSA\Account\Controller\Controller {

    public const ACCOUNT_ROUTE_ID            = 1;
    public const TEMPLATE_NAME_PERSONAL_INFO = "personal_info.html";

    private $l10n              = null;
    private $templateManager   = null;
    private $id                = null;
    private $user              = null;
    private $userService       = null;
    private $permissionManager = null;
    private $fileService       = null;
    private $fileManager       = null;
    private $rawFileService    = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUser $user
        , UserService $userService
        , IPermissionRepository $permissionManager
        , FileService $fileService
        , IFileManager $fileManager
        , RawFileService $rawFileService
    ) {
        $this->l10n              = $l10n;
        $this->templateManager   = $templateManager;
        $this->user              = $user;
        $this->userService       = $userService;
        $this->id                = Controller::ACCOUNT_ROUTE_ID;
        $this->permissionManager = $permissionManager;
        $this->fileService       = $fileService;
        $this->fileManager       = $fileManager;
        $this->rawFileService    = $rawFileService;

        parent::__construct(
            $templateManager
            , $l10n
        );
    }

    public function onCreate(...$params): void {
        parent::onCreate($params);

        parent::setPermission(
            PermissionFactory::getDefaultPermission()
        );

    }

    public function create(): void {

        $defaultImage = $this->fileService->getDefaultProfileImage();

        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getProfileImagePath($this->user)
            )
        );

        $src = $this->rawFileService->stringToBase64($file->getFullPath());

        $this->templateManager->replace(
            Controller::TEMPLATE_NAME_PERSONAL_INFO
            , [


                // buttons
                "upload"                 => $this->l10n->translate("Upload")
                , "delete"               => $this->l10n->translate("Delete")
                , "save"                 => $this->l10n->translate("Save")

                // title
                , "personalInformation"  => $this->l10n->translate("Personal Information")
                , "profilepicture"       => $this->l10n->translate("Profile Image")

                // labels
                , "firstName"            => $this->l10n->translate("Name")
                , "email"                => $this->l10n->translate("Email")
                , "phone"                => $this->l10n->translate("Phone Number")
                , "website"              => $this->l10n->translate("Web")
                , "lastName"             => $this->l10n->translate("Last Name")
                , "userHash"             => $this->l10n->translate("User Hash")
                , "userName"             => $this->l10n->translate("Username")

                // Placeholder
                , "firstNamePlaceholder" => $this->l10n->translate("First Name")
                , "lastNamePlaceHolder"  => $this->l10n->translate("Last Name")
                , "emailPlaceHolder"     => $this->l10n->translate("Email")
                , "phonePlaceholder"     => $this->l10n->translate("Phone Number")
                , "webPlaceHolder"       => $this->l10n->translate("Website")
                , "userNamePlaceholder"  => $this->l10n->translate("Username")

                // objects
                , "user"                 => $this->user
                , "imageURL"             => $src
                , "defaultImage"         => $defaultImage
            ]
        );

        parent::setAppContent($this->templateManager->render(Controller::TEMPLATE_NAME_PERSONAL_INFO));
    }

    public function afterCreate(): void {
    }

}
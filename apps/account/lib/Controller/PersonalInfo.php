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

namespace KSA\Account\Controller;

use Keestash\Core\Service\File\FileService;
use Keestash\Core\Service\File\RawFile\RawFileService;
use Keestash\Core\Service\UserService;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\FileManager\IFileManager;
use KSP\Core\Manager\TemplateManager\ITemplateManager;
use KSP\L10N\IL10N;

class PersonalInfo {

    private $templateManager = null;
    private $l10n            = null;
    private $user            = null;
    private $userService     = null;
    private $fileManager     = null;
    private $rawFileService  = null;
    private $fileService     = null;

    public function __construct(
        ITemplateManager $templateManager
        , IL10N $l10n
        , IUser $user
        , UserService $userService
        , IFileManager $fileManager
        , RawFileService $rawFileService
        , FileService $fileService
    ) {
        $this->templateManager = $templateManager;
        $this->l10n            = $l10n;
        $this->user            = $user;
        $this->userService     = $userService;
        $this->fileManager     = $fileManager;
        $this->rawFileService  = $rawFileService;
        $this->fileService     = $fileService;
    }

    public function handle() {
        $defaultImage = $this->fileService->getDefaultProfileImage();

        $file = $this->fileManager->read(
            $this->rawFileService->stringToUri(
                $this->fileService->getProfileImagePath($this->user)
            )
        );

        $src = $this->rawFileService->stringToBase64($file->getFullPath());

        $this->templateManager->replace("personal_info.html",
            [
                "profilepicture"         => $this->l10n->translate("Profile Image")
                , "user"                 => $this->user
                , "upload"               => $this->l10n->translate("Upload")
                , "delete"               => $this->l10n->translate("Delete")
                , "personalInformation"  => $this->l10n->translate("Personal Information")
                , "name"                 => $this->l10n->translate("Name")
                , "firstNamePlaceholder" => $this->l10n->translate("First Name")
                , "lastNamePlaceHolder"  => $this->l10n->translate("Last Name")
                , "email"                => $this->l10n->translate("Email")
                , "phone"                => $this->l10n->translate("Phone Number")
                , "website"              => $this->l10n->translate("Web")
                , "emailPlaceHolder"     => $this->l10n->translate("Email")
                , "phonePlaceholder"     => $this->l10n->translate("Phone Number")
                , "webPlaceHolder"       => $this->l10n->translate("Website")
                , "save"                 => $this->l10n->translate("Save")
                , "imageURL"             => $src
                , "defaultImage"         => $defaultImage
                , "lastName"             => $this->l10n->translate("Last Name")
                , "userHash"             => $this->l10n->translate("User Hash")
            ]
        );
        return $this->templateManager->render("personal_info.html");
    }

}
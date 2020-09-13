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

namespace Keestash\Core\Service\File;

use DateTime;
use Keestash;
use Keestash\Core\DTO\File\File;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;

class FileService {

    public const DEFAULT_IMAGE_FILE_ID = 1;

    private RawFileService $rawFileService;

    public function __construct(RawFileService $rawFileService) {
        $this->rawFileService = $rawFileService;
    }

    // TODO include default ?!
    public function getProfileImagePath(?IUser $user): string {

        if (null === $user) {
            return $this->getDefaultImage()->getFullPath();
        }

        $imagePath = $this->getProfileImage($user);
        $imagePath = realpath($imagePath);

        if (false === $imagePath) {
            return $this->getDefaultImage()->getFullPath();
        }

        return $imagePath;
    }

    public function getDefaultImage(): IFile {
        $name = 'profile-picture';
        $dir  = Keestash::getServer()->getAssetRoot() . "/img/";
        $dir  = str_replace("//", "/", $dir);
        $path = "$dir/$name.png";

        $file = new File();
        $file->setId(FileService::DEFAULT_IMAGE_FILE_ID);
        $file->setContent(
            file_get_contents($path)
        );
        $file->setCreateTs(new DateTime());
        $file->setDirectory($dir);
        $file->setExtension(IExtension::PNG);
        $file->setHash(md5_file($path));
        $file->setMimeType($this->rawFileService->getMimeType($path));
        $file->setName($name);
        $file->setSize(filesize($path));
        $file->setOwner(
            Keestash::getServer()->getSystemUser()
        );
        return $file;
    }

    public function getProfileImage(IUser $user): string {
        $name = $this->getProfileImageName($user);
        $path = Keestash::getServer()->getImageRoot() . "/" . $name;
        return str_replace("//", "/", $path);
    }

    public function getProfileImageName(IUser $user): string {
        return "profile_image_{$user->getId()}";
    }

}

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

use Keestash;
use Keestash\Core\Service\File\RawFile\RawFileService;
use KSP\Core\DTO\IUser;

class FileService {

    private $rawFileService = null;

    public function __construct(RawFileService $rawFileService) {
        $this->rawFileService = $rawFileService;
    }

    public function getProfileImageName(IUser $user): string {
        return "profile_image_{$user->getId()}";
    }

    public function getDefaultProfileImage(): string {
        $image = Keestash::getServer()->getAssetRoot() . "/img/profile-picture.PNG";
        return str_replace("//", "/", $image);
    }

    public function getProfileImage(IUser $user): string {
        $name = $this->getProfileImageName($user);
        $path = Keestash::getServer()->getImageRoot() . "/" . $name;
        return str_replace("//", "/", $path);
    }

    public function getProfileImagePath(?IUser $user): string {

        if (null === $user) {
            return $this->getDefaultProfileImage();
        }

        $imagePath = $this->getProfileImage($user);
        $imagePath = realpath($imagePath);

        if (false === $imagePath) {
            return $this->getDefaultProfileImage();
        }

        return $imagePath;
    }

}
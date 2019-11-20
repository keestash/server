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

namespace Keestash\Core\Manager\AssetManager;

use DirectoryIterator;
use Keestash;
use Keestash\Server;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\AssetManager\IAssetManager;
use xobotyi\MimeType;

class AssetManager implements IAssetManager {

    public function getProfilePicture(IUser $user): ?string {
        $dir = Keestash::getServer()->getImageRoot();

        // TODO check if dir exists !
        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if (false !== strpos($fileInfo->getFilename(), (string) $user->getId())) {
                return $fileInfo->getRealPath();
            }

        }

        return null;
    }

    public function getDefaultImage(): string {
        return Keestash::getBaseURL(false, true) . "/asset/img/profile-picture.png";
    }


    public function uriToBase64(string $uri): ?string {

        $content = @file_get_contents($uri);
        if (false === $content) return null;
        $tempNae = tempnam(sys_get_temp_dir(), "pp_");
        $put     = file_put_contents($tempNae, $content);
        if (false === $put) return null;

        $imgData = base64_encode($content);

        $base64 = 'data: ' . mime_content_type($tempNae) . ';base64,' . $imgData;

        return $base64;

    }

}
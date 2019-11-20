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

    private const PERMISSION = 0777;

    public function writeProfilePicture(string $base64, IUser $user): bool {
        $data    = explode(',', $base64);
        $base64  = $data[1];
        $decoded = base64_decode($base64);
        if (false === $decoded) return false;

        $allowedImages = Keestash::getServer()->query(Server::ALLOWED_IMAGE_MIME_TYPES);
        $f             = finfo_open();
        $mimeType      = finfo_buffer($f, $decoded, FILEINFO_MIME_TYPE);
        $extensions    = MimeType::getExtensions($mimeType);
        $extension     = $extensions[1];

        if (!in_array($mimeType, $allowedImages)) return false;

        $dir = Keestash::getServer()->getImageRoot();

        if (false === is_dir($dir)) {
            $created = mkdir($dir, AssetManager::PERMISSION, true);
            if (false === $created) return false;
        }

        $filename = $dir . "{$user->getId()}.$extension";
        $put      = file_put_contents($filename, $decoded);
        chmod($filename, AssetManager::PERMISSION);

        if (false === $put) return false;

        return true;

    }

    public function removeProfilePicture(IUser $user): bool {
        $dir = Keestash::getServer()->getImageRoot();

        foreach (new DirectoryIterator($dir) as $fileInfo) {
            if ($fileInfo->isDot()) continue;
            if (false !== strpos($fileInfo->getFilename(), (string) $user->getId())) {
                return unlink($fileInfo->getRealPath());
            }
        }

        return false;

    }

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
        return Keestash::getServer()->getServerRoot() . "/asset/img/profile-picture.png";
    }


    public function uriToBase64(string $uri, bool $rawBase64 = false): ?string {

        $content = @file_get_contents($uri);
        if (false === $content) return null;
        $tempNae = tempnam(sys_get_temp_dir(), "pp_");
        $put     = file_put_contents($tempNae, $content);
        if (false === $put) return null;

        $base64 = $imgData = base64_encode($content);

        if (false === $rawBase64) {
            $base64 = 'data: ' . mime_content_type($tempNae) . ';base64,' . $imgData;
        }

        return $base64;

    }

}
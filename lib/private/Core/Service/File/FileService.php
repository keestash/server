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
use Keestash\Core\DTO\URI\URL\URL;
use Keestash\Exception\File\FileNotFoundException;
use KSP\Core\DTO\File\IExtension;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\IFileService;
use KSP\Core\Service\File\RawFile\IRawFileService;
use Laminas\Config\Config;

class FileService implements IFileService {

    public const DEFAULT_IMAGE_FILE_ID   = 1;
    public const DEFAULT_AVATAR_FILE_ID  = 2;
    public const DEFAULT_NODE_AVATAR     = "default-avatar";
    public const DEFAULT_PROFILE_PICTURE = "profile-picture";

    // TODO include default ?!
    private IRawFileService $rawFileService;
    private Config          $config;
    private IFileRepository $fileRepository;

    public function __construct(
        IRawFileService   $rawFileService
        , Config          $config
        , IFileRepository $fileRepository
    ) {
        $this->rawFileService = $rawFileService;
        $this->config         = $config;
        $this->fileRepository = $fileRepository;
    }

    public function getProfileImagePath(IUser $user): IUniformResourceIdentifier {
        $imagePath = $this->getProfileImage($user);
        $imagePath = realpath($imagePath->getIdentifier());

        if (false === $imagePath) {
            $imagePath = $this->getDefaultImage()->getFullPath();
        }

        $url = new URL();
        $url->setIdentifier($imagePath);
        return $url;
    }

    public function getDefaultImage(): IFile {
        $name = FileService::DEFAULT_PROFILE_PICTURE;
        $dir  = $this->config->get(Keestash\ConfigProvider::ASSET_PATH) . '/img/';
        $dir  = str_replace("//", "/", $dir);
        $path = "$dir/$name.png";

        $file = new File();
        $file->setId(FileService::DEFAULT_IMAGE_FILE_ID);
        $file->setContent(
            (string) file_get_contents($path)
        );
        $file->setCreateTs(new DateTime());
        $file->setDirectory($dir);
        $file->setExtension(IExtension::PNG);
        $file->setHash((string) md5_file($path));
        $file->setMimeType($this->rawFileService->getMimeType($path));
        $file->setName($name);
        $file->setSize((int) filesize($path));
        return $file;
    }

    public function getProfileImage(IUser $user): IUniformResourceIdentifier {
        $url       = new URL();
        $name      = $this->getProfileImageName($user);
        $imagePath = $this->config->get(Keestash\ConfigProvider::IMAGE_PATH);
        $path      = $imagePath . "/" . $name;
        $url->setIdentifier(str_replace("//", "/", $path));
        return $url;
    }

    public function getProfileImageName(IUser $user): string {
        return "profile_image_{$user->getId()}";
    }

    public function read(IUniformResourceIdentifier $uri): IFile {
        $path = $uri->getIdentifier();
        if (false === is_file($path)) {
            throw new FileNotFoundException();
        }
        return $this->fileRepository->getByUri($uri);
    }

    public function removeProfileImage(IUser $user): void {
        $imagePath = $this->getProfileImage($user);
        $imagePath = realpath($imagePath->getIdentifier());

        if (false === $imagePath) {
            return;
        }

        unlink($imagePath);
    }

}

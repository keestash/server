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
use KSP\Core\DTO\URI\IUniformResourceIdentifier;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\File\IFileRepository;
use KSP\Core\Service\File\IFileService;
use Laminas\Config\Config;

class FileService implements IFileService {

    public const DEFAULT_IMAGE_FILE_ID   = 1;
    public const DEFAULT_AVATAR_FILE_ID  = 2;
    public const DEFAULT_NODE_AVATAR     = "default-avatar";
    public const DEFAULT_PROFILE_PICTURE = "profile-picture";

    // TODO include default ?!
    private RawFileService  $rawFileService;
    private Config          $config;
    private IFileRepository $fileRepository;

    public function __construct(
        RawFileService    $rawFileService
        , Config          $config
        , IFileRepository $fileRepository
    ) {
        $this->rawFileService = $rawFileService;
        $this->config         = $config;
        $this->fileRepository = $fileRepository;
    }

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
        $file->setMimeType((string) $this->rawFileService->getMimeType($path));
        $file->setName($name);
        $file->setSize((int) filesize($path));
        return $file;
    }

    public function getProfileImage(IUser $user): string {
        $name      = $this->getProfileImageName($user);
        $imagePath = $this->config->get(Keestash\ConfigProvider::IMAGE_PATH);
        $path      = $imagePath . "/" . $name;
        return str_replace("//", "/", $path);
    }

    public function getProfileImageName(IUser $user): string {
        return "profile_image_{$user->getId()}";
    }

    public function getAvatarName(int $nodeId): string {
        return "node_avatar_$nodeId";
    }

    public function read(?IUniformResourceIdentifier $uri): ?IFile {
        if (null === $uri) return null;
        $path = $uri->getIdentifier();
        if (false === is_file($path)) return null;
        return $this->fileRepository->getByUri($uri);
    }

}

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

namespace Keestash\Core\Service;

use Keestash\Core\Manager\AssetManager\AssetManager;
use KSP\Core\DTO\IUser;
use KSP\Core\Manager\AssetManager\IAssetManager;

class AssetService {

    /** @var IAssetManager|null|AssetManager */
    private $assetManager = null;

    public function __construct(IAssetManager $assetManager) {
        $this->assetManager = $assetManager;
    }

    private function getUri(IUser $user): ?string {
        $picture = $this->assetManager->getProfilePicture($user);
        if (null === $picture) {
            $picture = $this->assetManager->getDefaultImage();
        }
        return $picture;
    }

    public function getUserProfilePicture(?IUser $user, bool $rawBase64 = false): ?string {
        if (null === $user) return null;
        $picture = $this->getUri($user);
        return $this->assetManager->uriToBase64($picture, $rawBase64);
    }

    public function getUserProfileForRestApi(IUser $user): ?string {
        $picture = $this->getUri($user);
        if (null === $picture) return null;
        return file_get_contents($picture);
    }

}
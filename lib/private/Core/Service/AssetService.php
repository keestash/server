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

    public function getUserProfilePicture(IUser $user): ?string {
        $picture = $this->assetManager->getProfilePicture($user);
        if (null === $picture) {
            $picture = $this->assetManager->getDefaultImage();
        }

        return $this->assetManager->uriToBase64($picture);
    }

}
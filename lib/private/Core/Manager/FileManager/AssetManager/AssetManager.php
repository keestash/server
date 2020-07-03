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

namespace Keestash\Core\Manager\FileManager\AssetManager;

use Keestash\Core\Manager\FileManager\FileManager;
use KSP\Core\DTO\File\Asset\IAsset;
use KSP\Core\DTO\File\IFile;
use KSP\Core\Manager\FileManager\AssetManager\IAssetManager;

class AssetManager extends FileManager implements IAssetManager {

    public function write(IFile $file): bool {
        if ($file instanceof IAsset) {
            return parent::write($file);
        }
        return false;
    }

    public function verifyFile(IFile $file): bool {
        if ($file instanceof IAsset) {
            return parent::verifyFile($file);
        }
        return false;
    }


}

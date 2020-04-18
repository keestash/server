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

namespace Keestash\Core\System\Installation\Verification;

use doganoo\PHPUtil\FileSystem\DirHandler;
use Keestash;

/**
 * Class HasDataDirs
 * @package Keestash\Core\System\Installation\Verification
 */
class HasDataDirs extends AbstractVerification {

    private const PERMISSION = 0777; // TODO change permission

    public function hasProperty(): bool {
        $dataRoot  = Keestash::getServer()->getDataRoot();
        $imageRoot = Keestash::getServer()->getImageRoot();

        $hasDataRoot  = $this->checkAndCreateDirIfNecessary($dataRoot);
        $hasImageRoot = $this->checkAndCreateDirIfNecessary($imageRoot);

        return
            true === $hasDataRoot
            && true === $hasImageRoot;
    }

    private function checkAndCreateDirIfNecessary(string $path): bool {

        $dirHandler = new DirHandler($path);
        $valid      = true;

        if (false === $dirHandler->exists()) {
            // we first try to create
            $dirCreated = $dirHandler->mkdir(
                HasDataDirs::PERMISSION
            );

            if (false === $dirCreated) {
                parent::addMessage("has_data_dirs", "{$dirHandler->getPath()} could not be created");
                $valid = false;
            }
        }
        return $valid;
    }

}

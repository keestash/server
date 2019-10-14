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

namespace Keestash\Core\System\Installation;

use doganoo\PHPUtil\FileSystem\DirHandler;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;

abstract class LockHandler extends DirHandler {

    public function __construct() {
        parent::__construct(
            Keestash::getServer()->getLockRoot()
        );
    }

    public function isLocked(): bool {
        return parent::hasFile(
            $this->getFileName()
        );
    }

    public function lock(): bool {
        if (true === $this->isLocked()) return true;
        return parent::createFile(
            $this->getFileName()
            , false
            , (string) getmypid()
        );
    }

    public function unlock(): bool {
        if (false === $this->isLocked()) return true;

        $fileDeleted = parent::deleteFile(
            $this->getFileName()
        );

        if (false === $fileDeleted) return false;

        return parent::rmdir(
            $this->getPath()
        );

    }

    public abstract function getFileName(): string;

}
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

use doganoo\PHPUtil\Datatype\StringClass;
use doganoo\PHPUtil\Log\FileLogger;
use Keestash;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

class DirsWritable extends AbstractVerification {

    private const EXCLUDES = [
        "node_modules"
        , "vendor"
        , ".gitignore"
        , ".editorconfig"
        , ".idea"
        , ".git"
        , "test"
    ];

    public function hasProperty(): bool {
        $appRoot   = Keestash::getServer()->getServerRoot();
        $directory = new RecursiveDirectoryIterator($appRoot);
        $iterator  = new RecursiveIteratorIterator($directory);
        $valid     = true;

        /** @var SplFileInfo $info */
        foreach ($iterator as $info) {
            if (true === $this->isDot($info)) continue;
            if (true === $this->isExcluded($info)) continue;
            if (true === $info->isLink()) continue;

            $validElement = $this->handleDir($info);
            $valid        = $valid && $validElement;
        }

        return $valid;
    }

    private function isExcluded(SplFileInfo $info): bool {
        foreach (DirsWritable::EXCLUDES as $exclude) {
            $class = new StringClass($info->getRealPath());
            if (true === $class->containsIgnoreCase($exclude)) return true;
        }
        return false;
    }

    private function isDot(SplFileInfo $info): bool {
        return $info->getBasename() === "." || $info->getBasename() === "..";
    }

    private function handleDir(SplFileInfo $info): bool {
        if (false === $info->isReadable()) {
            parent::addMessage("dir_readable", $info->getRealPath());
            FileLogger::debug("{$info->getRealPath()} is not readable");
            return false;
        }

        if (false === $info->isWritable()) {
            parent::addMessage("dir_writable", $info->getRealPath());
            FileLogger::debug("{$info->getRealPath()} is not writable");
            return false;
        }
        return true;
    }

}
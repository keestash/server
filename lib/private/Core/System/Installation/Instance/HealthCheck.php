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

namespace Keestash\Core\System\Installation\Instance;


use Keestash;
use Keestash\Exception\KSException;

class HealthCheck {

    private $configDir     = null;
    private $sysConfigDir  = null;
    private $sysConfigFile = null;

    public function __construct() {
        $dir = Keestash::getServer()->getConfigRoot();

        $this->configDir     = $dir;
        $this->sysConfigDir  = $dir . "/sys_config";
        $this->sysConfigFile = $this->sysConfigDir . "/sys_config.json";

    }

    public function readInstallation(): bool {
        if (false === $this->hasFilesAndDirs()) return false;
        $array = $this->readSysConfigFile();
        if (null === $array) return false;
        return isset($array['installed']) && isset($array['instance_hash']);
    }

    private function hasFilesAndDirs(): bool {
        if (false === is_dir($this->sysConfigDir)) return false;
        if (false === is_file($this->sysConfigFile)) return false;
        return true;
    }

    private function createFilesAndDirs(): bool {
        if (true === $this->hasFilesAndDirs()) return true;
        $dirCreated  = false;
        $fileCreated = false;
        if (false === is_dir($this->sysConfigDir)) {
            $dirCreated = mkdir($this->sysConfigDir);
        }

        if (false === is_file($this->sysConfigFile)) {
            $fileCreated = touch($this->sysConfigFile);
        }

        return true === $dirCreated && true === $fileCreated;
    }

    private function readSysConfigFile(): ?array {
        if (false === $this->hasFilesAndDirs()) return null;
        $content = file_get_contents(
            $this->sysConfigFile
        );
        if (null === $content) return null;
        if ("" === trim($content)) return [];
        return json_decode($content, true);
    }

    public function storeInstallation(): bool {
        if (false === $this->createFilesAndDirs()) {
            throw new KSException("could not create");
        }

        $array = $this->readSysConfigFile();

        if (null === $array) {
            throw new KSException("could not read file");
        }

        $array['installed']     = true;
        $array['instance_hash'] = md5(uniqid());

        $put = file_put_contents($this->sysConfigFile, json_encode($array, JSON_PRETTY_PRINT));

        if (false === $put) {
            throw new KSException("could not create file");
        }
        return true;
    }


}
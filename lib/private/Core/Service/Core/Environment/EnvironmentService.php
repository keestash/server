<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\Service\Core\Environment;

use Keestash\ConfigProvider;
use KSP\Core\Service\Core\Environment\IEnvironmentService;

class EnvironmentService implements IEnvironmentService {

    private ?string $env = null;

    public function getEnv(bool $force = false): string {
        if (null === $this->env || true === $force) {
            $this->env = (string) getenv(ConfigProvider::ENVIRONMENT_KEY);
        }
        return $this->env;
    }

    public function isApi(): bool {
        return $this->getEnv() === ConfigProvider::ENVIRONMENT_API;
    }

    public function isWeb(): bool {
        return $this->getEnv() === ConfigProvider::ENVIRONMENT_WEB;
    }

    public function isConsole(): bool {
        return $this->getEnv() === ConfigProvider::ENVIRONMENT_CONSOLE;
    }

    public function isUnitTest(): bool {
        return $this->getEnv() === ConfigProvider::ENVIRONMENT_UNIT_TEST;
    }

    public function setEnv(string $env): bool {
        return putenv(ConfigProvider::ENVIRONMENT_KEY . "=" . $env);
    }

}
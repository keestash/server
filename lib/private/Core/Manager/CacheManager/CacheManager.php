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

namespace Keestash\Core\Manager\CacheManager;

use KSP\Core\Manager\CacheManager\ICacheManager;

class CacheManager implements ICacheManager {

    private string $configured = ICacheManager::SERVICE_REDIS;

    public function getConfiguredService(): string {
        $extensions = get_loaded_extensions();

        if (false === in_array($this->configured, $extensions, true)) {
            return $this->configured;
        }
        return ICacheManager::SERVICE_NULL;
    }

}
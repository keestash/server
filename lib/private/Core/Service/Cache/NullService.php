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

namespace Keestash\Core\Service\Cache;

use KSP\Core\Service\Cache\ICacheService;

/**
 * Class NullServer
 * @package Keestash\Core\Cache
 */
class NullService implements ICacheService {

    #[\Override]
    public function connect(): void {

    }

    /**
     * @param string $key
     * @param mixed  $value
     * @return bool
     */
    #[\Override]
    public function set(string $key, $value): bool {
        return true;
    }

    #[\Override]
    public function get(string $key) {
        return null;
    }

    #[\Override]
    public function exists(string $key): bool {
        return false;
    }

}
<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

namespace KST\Service\Core\Cache;

use Keestash\Core\Service\Cache\NullService;
use KSP\Core\Service\Cache\ICacheService;

class RedisService implements ICacheService {

    private ICacheService $cacheService;

    public function __construct(NullService $nullService) {
        $this->cacheService = $nullService;
    }

    public function connect(): void {
        $this->cacheService->connect();
    }

    public function set(string $key, $value): bool {
        return $this->cacheService->set($key, $value);
    }

    public function get(string $key) {
        return $this->cacheService->get($key);
    }

    public function exists(string $key): bool {
        return $this->cacheService->exists($key);
    }

}
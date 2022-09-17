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

namespace Keestash\Core\System\RateLimit;

use RateLimit\ConfigurableRateLimiter;
use RateLimit\Exception\LimitExceeded;
use RateLimit\RateLimiter;

class FileRateLimiter extends ConfigurableRateLimiter implements RateLimiter {

    private array $store = [];

    private function loadStore(): void {
        $fileName = __DIR__ . '/tmp.json';
        if (file_exists($fileName)) {
            $this->store = json_decode(
                (string) file_get_contents($fileName)
                , true
            );
        }
    }

    private function writeStore(): void {
        file_put_contents(
            __DIR__ . '/tmp.json'
            , json_encode(
                $this->store
            )
        );
    }

    public function limit(string $identifier): void {
        $this->loadStore();
        $key = $this->key($identifier);

        $current = $this->hit($key);
        $this->writeStore();
        if ($current > $this->rate->getOperations()) {
            throw LimitExceeded::for($identifier, $this->rate);
        }
    }

    private function key(string $identifier): string {
        $interval = $this->rate->getInterval();
        return "$identifier:$interval:" . floor(time() / $interval);
    }

    private function hit(string $key): int {
        if (!isset($this->store[$key])) {
            $this->store[$key] = [
                'current'    => 1,
                'reset_time' => time() + $this->rate->getInterval(),
            ];
        } elseif ($this->store[$key]['current'] <= $this->rate->getOperations()) {
            $this->store[$key]['current']++;
        }

        return $this->store[$key]['current'];
    }

}
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

namespace Keestash\Core\Manager\CookieManager;


use DateTime;
use Keestash;
use KSP\Core\Manager\CookieManager\ICookieManager;

class CookieManager implements ICookieManager {

    public function set(string $key, string $value, int $expireTs = 0): bool {
        return setcookie(
            $key
            , $value
            , $expireTs
            , ICookieManager::PATH_ENTIRE_DOMAIN
            , Keestash::getBaseURL(false, false)
        );
    }

    public function get(string $key, ?string $default = null): ?string {
        return $this->getAll()[$key] ?? null;
    }

    public function getAll(): array {
        return $_COOKIE;
    }

    public function kill(string $key): bool {
        return $this->set($key, "", (new DateTime())->getTimestamp() - 3600);
    }

    public function killAll(): void {
        foreach ($_COOKIE as $key => $value) {
            $this->kill($key);
        }
        $_COOKIE = [];
        unset($_COOKIE);
    }

}
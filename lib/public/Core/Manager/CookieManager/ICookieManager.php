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

namespace KSP\Core\Manager\CookieManager;

use KSP\Core\Manager\IManager;

/**
 * Interface ICookieManager
 * @package KSP\Core\Manager\CookieManager
 */
interface ICookieManager extends IManager {

    public const COOKIE_PATH_ENTIRE_PATH = "/";
    public const COOKIE_SECURE           = false;
    public const COOKIE_HTTP_ONLY        = true;

    /**
     * @param string $key
     * @param string $value
     * @param int    $expireTs
     * @return bool
     */
    public function set(string $key, string $value, int $expireTs = 0): bool;

    /**
     * @param string      $key
     * @param string|null $default
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string;

    /**
     * @return array
     */
    public function getAll(): array;

    /**
     * @param string $key
     * @return bool
     */
    public function kill(string $key): bool;

    /**
     *
     */
    public function killAll(): void;

}
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

namespace KSP\Core\Manager\SessionManager;

use KSP\Core\Manager\IManager;

interface ISessionManager extends IManager {

    public const SESSION_GC_MAX_LIFETIME      = 3600;
    public const SESSION_GC_MAX_LIFETIME_NAME = "session.gc_maxlifetime";

    /**
     * @param string $name
     * @param string $value
     * @return bool
     */
    public function set(string $name, string $value): bool;

    /**
     * @param string      $name
     * @param string|null $default
     * @return string|null
     */
    public function get(string $name, ?string $default = null): ?string;

    /**
     * @param string $name
     * @return void
     */
    public function kill(string $name): void;

    /**
     * @return void
     */
    public function killAll(): void;

}
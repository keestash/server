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

namespace KSP\Core\Service\HTTP;

interface IPersistenceService {

    public function getSessionValue(string $key, ?string $default = null): ?string;

    public function getCookieValue(string $key, ?string $default = null): ?string;

    public function getPersistenceValue(string $key, ?string $default = null): ?string;

    public function setSessionValue(string $key, string $value): bool;

    public function setCookieValue(string $key, string $value, int $expireTs = 0): bool;

    public function isPersisted(string $key): bool;

    public function killAll(): void;

    public function setPersistenceValue(string $key, string $value, int $expireTs = 0): bool;

}
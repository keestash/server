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

namespace KST\Service\Core\Service\HTTP;

use KSP\Core\Service\HTTP\IHTTPService;

class HTTPService implements IHTTPService {

    public function getBaseURL(bool $withScript = true, bool $forceIndex = false): string {
        return "keestash.test";
    }

    public function buildWebRoute(string $base): string {
        return "keestash.test";
    }

    public function getBaseAPIURL(): ?string {
        return "keestash.test";
    }

    public function getAssetURL(): ?string {
        return "keestash.test";
    }

    public function getLoginRoute(): string {
        return "keestash.test";
    }

}
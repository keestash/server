<?php /** @noinspection ALL */
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

namespace Keestash\Core\System\Installation\Verification;

use Keestash;
use KSP\Core\Backend\IBackend;

class DatabaseReachable extends AbstractVerification {

    public function hasProperty(): bool {
        /** @var IBackend $backend */
        $backend   = Keestash::getServer()->query(IBackend::class);
        $connected = $this->isConnected($backend);
        if (false === $connected) {
            parent::addMessage(
                "database_reachable", "Database is not reachable"
            );
        }
        return true === $connected;
    }

    private function isConnected(IBackend $backend): bool {
        $backend->connect();
        return $backend->isConnected();
    }

}
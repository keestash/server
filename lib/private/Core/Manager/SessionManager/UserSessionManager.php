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

namespace Keestash\Core\Manager\SessionManager;

use doganoo\PHPUtil\Util\DateTimeUtil;

class UserSessionManager extends SessionManager {

    public function setId(int $id): void {
        parent::set("user_id", $id);
    }

    public function updateTimestamp(): bool {
        return parent::set("time_stamp", DateTimeUtil::getUnixTimestamp());
    }

    public function isUserLoggedIn(): bool {
        if (null === $this->getUser()) return false;
        if (null === $this->getTimestamp()) return false;

        $now = DateTimeUtil::subtractHours(1);

        $ts = $this->getTimestamp();

        return $now->getTimestamp() < $ts;
    }

    public function getUser(): ?string {
        $userId = parent::get("user_id");
        if (is_int($userId) && intval($userId) > 0) return (string) $userId;
        return null;
    }

    public function getTimestamp(): ?int {
        return parent::get("time_stamp");
    }

}
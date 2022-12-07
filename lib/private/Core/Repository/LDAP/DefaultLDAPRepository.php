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

namespace Keestash\Core\Repository\LDAP;

use KSP\Core\DTO\LDAP\IConnection;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\LDAP\ILDAPUserRepository;

class DefaultLDAPRepository implements ILDAPUserRepository {

    public function add(IConnection $connection, IUser $user): void {
        // silence is golden
    }

    public function removeByConnection(IConnection $connection): void {
        // silence is golden
    }

    public function isLDAPUser(IUser $user): bool {
        return false;
    }

}
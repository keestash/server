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

use Keestash\Exception\LDAP\ConnectionNotCreatedException;
use Keestash\Exception\LDAP\LDAPException;
use KSP\Core\DTO\LDAP\IConnection;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\LDAP\IConnectionRepository;

class DefaultConnectionRepository implements IConnectionRepository {

    public function add(IConnection $connection): void {
        throw new ConnectionNotCreatedException();
    }

    public function getConnection(string $host): IConnection {
        throw new LDAPException();
    }

    public function getActiveConnection(): IConnection {
        throw new LDAPException();
    }

    public function getConnectionByUser(IUser $user): IConnection {
        throw new LDAPException();
    }

    public function remove(IConnection $connection): void {
        // silence is golden
    }

    public function deactivate(IConnection $connection): void {
        // silence is golden
    }

}
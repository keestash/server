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

namespace KSP\Core\Repository\LDAP;

use Doctrine\DBAL\Exception;
use Keestash\Exception\LDAP\ConnectionNotCreatedException;
use Keestash\Exception\LDAP\LDAPException;
use Keestash\Exception\Repository\TooManyRowsException;
use KSP\Core\DTO\LDAP\IConnection;
use KSP\Core\DTO\User\IUser;

interface IConnectionRepository {

    /**
     * @param IConnection $connection
     * @return void
     * @throws ConnectionNotCreatedException
     * @throws Exception
     */
    public function add(IConnection $connection): void;

    /**
     * @param string $host
     * @return IConnection
     * @throws Exception
     * @throws LDAPException
     */
    public function getConnection(string $host): IConnection;

    /**
     * @return IConnection
     * @throws Exception
     * @throws LDAPException
     * @throws TooManyRowsException
     */
    public function getActiveConnection(): IConnection;

    public function getConnectionByUser(IUser $user): IConnection;

    /**
     * @param IConnection $connection
     * @return void
     * @throws LDAPException
     */
    public function remove(IConnection $connection): void;

    public function deactivate(IConnection $connection): void;

}
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

namespace Keestash\Core\DTO\LDAP;

use DateTimeInterface;
use KSP\Core\DTO\LDAP\IConnection;

readonly final class Connection implements IConnection {

    public function __construct(
        private string             $host,
        private string             $port,
        private string             $userDn,
        private string             $password,
        private string             $baseDn,
        private ?DateTimeInterface $activeTs,
        private DateTimeInterface  $createTs
    ) {
    }

    /**
     * @return string
     */
    #[\Override]
    public function getHost(): string {
        return $this->host;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getPort(): string {
        return $this->port;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getUserDn(): string {
        return $this->userDn;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @return string
     */
    #[\Override]
    public function getBaseDn(): string {
        return $this->baseDn;
    }

    /**
     * @return ?DateTimeInterface
     */
    #[\Override]
    public function getActiveTs(): ?DateTimeInterface {
        return $this->activeTs;
    }

    /**
     * @return DateTimeInterface
     */
    #[\Override]
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'host'        => $this->getHost()
            , 'port'      => $this->getPort()
            , 'user_dn'   => $this->getUserDn()
            , 'password'  => $this->getPassword()
            , 'base_dn'   => $this->getBaseDn()
            , 'active_ts' => $this->getActiveTs()
            , 'create_ts' => $this->getCreateTs()
        ];
    }

}

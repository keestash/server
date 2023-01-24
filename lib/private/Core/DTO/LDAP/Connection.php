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

class Connection implements IConnection {

    private string             $host;
    private string             $port;
    private string             $userDn;
    private string             $password;
    private string             $baseDn;
    private ?DateTimeInterface $activeTs;
    private DateTimeInterface  $createTs;

    public function __construct(
        string               $host
        , string             $port
        , string             $userDn
        , string             $password
        , string             $baseDn
        , ?DateTimeInterface $activeTs
        , DateTimeInterface  $createTs
    ) {
        $this->host     = $host;
        $this->port     = $port;
        $this->userDn   = $userDn;
        $this->password = $password;
        $this->baseDn   = $baseDn;
        $this->activeTs = $activeTs;
        $this->createTs = $createTs;
    }

    /**
     * @return string
     */
    public function getHost(): string {
        return $this->host;
    }

    /**
     * @return string
     */
    public function getPort(): string {
        return $this->port;
    }

    /**
     * @return string
     */
    public function getUserDn(): string {
        return $this->userDn;
    }

    /**
     * @return string
     */
    public function getPassword(): string {
        return $this->password;
    }

    /**
     * @return string
     */
    public function getBaseDn(): string {
        return $this->baseDn;
    }

    /**
     * @return ?DateTimeInterface
     */
    public function getActiveTs(): ?DateTimeInterface {
        return $this->activeTs;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

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
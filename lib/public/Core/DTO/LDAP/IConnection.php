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

namespace KSP\Core\DTO\LDAP;

use DateTimeInterface;
use KSP\Core\DTO\Entity\IJsonObject;

interface IConnection extends IJsonObject {

    /**
     * @return string
     */
    public function getHost(): string;

    /**
     * @return string
     */
    public function getPort(): string;

    /**
     * @return string
     */
    public function getUserDn(): string;

    /**
     * @return string
     */
    public function getPassword(): string;

    /**
     * @return string
     */
    public function getBaseDn(): string;

    /**
     * @return ?DateTimeInterface
     */
    public function getActiveTs(): ?DateTimeInterface;

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface;

}
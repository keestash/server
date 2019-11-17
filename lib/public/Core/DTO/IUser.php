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

namespace KSP\Core\DTO;

use DateTime;
use JsonSerializable;

/**
 * Interface IUser
 * @package KSP\Core\DTO
 *
 * TODO move to right place
 */
interface IUser extends KSObject {

    public function getId(): int;

    public function getName(): string;

    public function getPassword(): string;

    public function getCreateTs(): DateTime;

    public function getDisplayName(): string;

    public function getFirstName(): string;

    public function geKSAstName(): string;

    public function getEmail(): string;

    public function getPhone(): string;

    public function getWebsite(): string;

    public function getLastLogin(): ?DateTime;

    public function getHash(): string;

}
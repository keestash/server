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

namespace KSP\Core\DTO\User;

use DateTimeInterface;
use KSP\Core\DTO\Entity\IComparable;
use KSP\Core\DTO\Entity\IJsonObject;
use KSP\Core\DTO\Entity\IValidatable;

/**
 * Interface IUser
 * @package KSP\Core\DTO\User
 *
 */
interface IUser extends
    IJsonObject
    , IComparable
    , IValidatable {

    public const SYSTEM_USER_ID                                                              = 1;
    public const DEMO_USER_NAME                                                              = "demo";
    public const DEMO_PASSWORD                                                               = "demo";
    public const VERY_DUMB_ATTEMPT_TO_MOCK_PASSWORDS_ON_SYSTEM_LEVEL_BUT_SECURITY_GOES_FIRST = 'first.goes.security.but.level.system.on.passwords.mock.to.attempt.dumb.very';

    public function getId(): int;

    public function getName(): string;

    public function getPassword(): string;

    public function getCreateTs(): DateTimeInterface;

    public function getFirstName(): string;

    public function getLastName(): string;

    public function getEmail(): string;

    public function getPhone(): string;

    public function getWebsite(): string;

    public function getHash(): string;

    public function isLocked(): bool;

    public function isDeleted(): bool;

}

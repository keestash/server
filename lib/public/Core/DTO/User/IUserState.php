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
use KSP\Core\DTO\Entity\IObject;

interface IUserState extends IObject {

    /** @var string USER_STATE_DELETE */
    public const USER_STATE_DELETE = "delete.state.user";
    /** @var string USER_STATE_LOCK */
    public const USER_STATE_LOCK = "lock.state.user";
    /** @var string USER_STATE_REQUEST_PW_CHANGE */
    public const USER_STATE_REQUEST_PW_CHANGE = "change.pw.request.state.user";

    public static function isValidState(string $state): bool;

    public function getId(): int;

    public function getUser(): IUser;

    public function getState(): string;

    public function getValidFrom(): DateTimeInterface;

    public function getStateHash(): ?string;

    public function getCreateTs(): DateTimeInterface;

}

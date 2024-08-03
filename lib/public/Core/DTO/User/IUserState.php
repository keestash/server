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
use Keestash\Core\DTO\User\UserStateName;
use KSP\Core\DTO\Entity\IObject;

interface IUserState extends IObject {

    public const string  USER_STATE_DELETE                   = "delete.state.user";
    public const string  USER_STATE_LOCK                     = "lock.state.user";
    public const string  USER_STATE_LOCK_CANDIDATE_STAGE_ONE = "one.stage.candidate.lock.state.user";
    public const string  USER_STATE_LOCK_CANDIDATE_STAGE_TWO = "two.stage.candidate.lock.state.user";
    public const string  USER_STATE_REQUEST_PW_CHANGE        = "change.pw.request.state.user";

    public function getId(): int;

    public function getUser(): IUser;

    public function getState(): UserStateName;

    public function getValidFrom(): DateTimeInterface;

    public function getStateHash(): ?string;

    public function getCreateTs(): DateTimeInterface;

}

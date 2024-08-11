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

namespace Keestash\Core\DTO\User;

use DateTimeInterface;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;

readonly class UserState implements IUserState {

    public function __construct(
        private int               $id,
        private IUser             $user,
        private UserStateName     $state,
        private DateTimeInterface $validFrom,
        private DateTimeInterface $createTs,
        private string            $stateHash
    ) {
    }

    public function getId(): int {
        return $this->id;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    public function getState(): UserStateName {
        return $this->state;
    }

    /**
     * @return DateTimeInterface
     */
    public function getValidFrom(): DateTimeInterface {
        return $this->validFrom;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @return string|null
     */
    public function getStateHash(): ?string {
        return $this->stateHash;
    }

}

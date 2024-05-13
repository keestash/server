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

final readonly class UserState implements IUserState {

    public function __construct(
        private int               $id,
        private IUser             $user,
        private string            $state,
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

    /**
     * @return string
     */
    public function getState(): string {
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

    public static function isValidState(string $state): bool {
        return in_array(
            $state, [
                IUserState::USER_STATE_DELETE
                , IUserState::USER_STATE_LOCK
                , IUserState::USER_STATE_REQUEST_PW_CHANGE
                , IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_ONE
                , IUserState::USER_STATE_LOCK_CANDIDATE_STAGE_TWO
            ]
            , true
        );
    }

}

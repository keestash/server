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

class UserState implements IUserState {

    private int               $id;
    private IUser             $user;
    private string            $state;
    private DateTimeInterface $validFrom;
    private DateTimeInterface $createTs;
    private ?string           $stateHash;

    public static function isValidState(string $state): bool {
        return in_array(
            $state, [
                IUserState::USER_STATE_DELETE
                , IUserState::USER_STATE_LOCK
                , IUserState::USER_STATE_REQUEST_PW_CHANGE
            ]
            , true
        );
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    /**
     * @param IUser $user
     */
    public function setUser(IUser $user): void {
        $this->user = $user;
    }

    /**
     * @return string
     */
    public function getState(): string {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void {
        $this->state = $state;
    }

    /**
     * @return DateTimeInterface
     */
    public function getValidFrom(): DateTimeInterface {
        return $this->validFrom;
    }

    /**
     * @param DateTimeInterface $validFrom
     */
    public function setValidFrom(DateTimeInterface $validFrom): void {
        $this->validFrom = $validFrom;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    /**
     * @param DateTimeInterface $createTs
     */
    public function setCreateTs(DateTimeInterface $createTs): void {
        $this->createTs = $createTs;
    }

    /**
     * @return string|null
     */
    public function getStateHash(): ?string {
        return $this->stateHash;
    }

    /**
     * @param string|null $stateHash
     */
    public function setStateHash(?string $stateHash): void {
        $this->stateHash = $stateHash;
    }

}

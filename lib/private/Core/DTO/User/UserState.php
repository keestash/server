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

use DateTime;
use KSP\Core\DTO\User\IUserState;
use KSP\Core\DTO\User\IJsonUser;

class UserState implements IUserState {

    /** @var int $id */
    private $id;
    /** @var IJsonUser $user */
    private $user;
    /** @var string $state */
    private $state;
    /** @var DateTime $validFrom */
    private $validFrom;
    /** @var DateTime $createTs */
    private $createTs;

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
     * @return IJsonUser
     */
    public function getUser(): IJsonUser {
        return $this->user;
    }

    /**
     * @param IJsonUser $user
     */
    public function setUser(IJsonUser $user): void {
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
     * @return DateTime
     */
    public function getValidFrom(): DateTime {
        return $this->validFrom;
    }

    /**
     * @param DateTime $validFrom
     */
    public function setValidFrom(DateTime $validFrom): void {
        $this->validFrom = $validFrom;
    }

    /**
     * @return DateTime
     */
    public function getCreateTs(): DateTime {
        return $this->createTs;
    }

    /**
     * @param DateTime $createTs
     */
    public function setCreateTs(DateTime $createTs): void {
        $this->createTs = $createTs;
    }


    public static function isValidState(string $state): bool {
        return in_array($state, [
            IUserState::USER_STATE_DELETE
            , IUserState::USER_STATE_LOCK
        ]);
    }

}

<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace Keestash\Core\Service\User\Event;

use Keestash\Core\DTO\Event\Event;
use KSP\Core\DTO\User\IUser;

class UserStateDeleteEvent extends Event {

    private string $stateType;
    private IUser  $user;

    public function __construct(string $stateType, IUser $user) {
        $this->stateType = $stateType;
        $this->user      = $user;
    }

    /**
     * @return string
     */
    public function getStateType(): string {
        return $this->stateType;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    public function jsonSerialize(): array {
        return [
            'stateType' => $this->getStateType()
            , 'user'    => $this->getUser()
        ];
    }

}
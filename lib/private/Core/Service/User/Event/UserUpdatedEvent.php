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

class UserUpdatedEvent extends Event {

    public function __construct(
        private readonly IUser    $updatedUser
        , private readonly IUser  $user
        , private readonly string $key
        , private readonly int    $priority = 99999999
    ) {
    }

    /**
     * @return IUser
     */
    public function getUpdatedUser(): IUser {
        return $this->updatedUser;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    /**
     * @return int
     */
    #[\Override]
    public function getPriority(): int {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getKey(): string {
        return $this->key;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'updatedUser' => $this->getUpdatedUser()
            , 'user'      => $this->getUser()
            , 'priority'  => $this->getPriority()
        ];
    }

}

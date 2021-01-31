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

use KSP\Core\DTO\User\IUser;
use Symfony\Contracts\EventDispatcher\Event;

class UserUpdatedEvent extends Event {

    private IUser $updatedUser;
    private IUser $oldUser;
    private bool  $updated;

    public function __construct(IUser $updatedUser, IUser $oldUser, bool $updated) {
        $this->updatedUser = $updatedUser;
        $this->oldUser     = $oldUser;
        $this->updated     = $updated;
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
    public function getOldUser(): IUser {
        return $this->oldUser;
    }

    /**
     * @return bool
     */
    public function isUpdated(): bool {
        return $this->updated;
    }

}
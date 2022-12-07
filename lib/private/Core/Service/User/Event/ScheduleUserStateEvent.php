<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use DateTimeImmutable;
use DateTimeInterface;
use Keestash\Core\DTO\Event\ReservedEvent;
use KSP\Core\DTO\User\IUser;

class ScheduleUserStateEvent extends ReservedEvent {

    private string            $stateType;
    private IUser             $user;
    private DateTimeInterface $reservedTs;

    public function __construct(
        string              $stateType
        , IUser             $user
        , DateTimeInterface $reservedTs = null
    ) {
        $this->stateType  = $stateType;
        $this->user       = $user;
        $this->reservedTs = null === $reservedTs
            ? new DateTimeImmutable()
            : $reservedTs;
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    public function getReservedTs(): DateTimeInterface {
        return $this->reservedTs;
    }

    /**
     * @return string
     */
    public function getStateType(): string {
        return $this->stateType;
    }

    public function jsonSerialize(): array {
        return [
            'user'         => $this->getUser()
            , 'reservedTs' => $this->getReservedTs()
            , 'stateType'  => $this->getStateType()
        ];
    }

}
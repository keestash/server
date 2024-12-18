<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace KSA\Register\Event;

use Keestash\Core\DTO\Event\Event;
use KSA\Register\Entity\Register\Event\Type;
use KSP\Core\DTO\User\IUser;

final class UserRegisteredEvent extends Event {

    public function __construct(
        private readonly IUser  $user,
        private readonly string $key,
        private readonly Type   $type,
        private readonly int    $priority
    ) {
    }

    /**
     * @return IUser
     */
    public function getUser(): IUser {
        return $this->user;
    }

    /**
     * @return Type
     */
    public function getType(): Type {
        return $this->type;
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
            'user'       => $this->getUser()
            , 'type'     => $this->getType()
            , 'priority' => $this->getPriority()
        ];
    }

}

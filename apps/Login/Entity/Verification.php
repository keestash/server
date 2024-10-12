<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2024> <Dogan Ucar>
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

namespace KSA\Login\Entity;

use doganoo\DI\Entity\IJsonEntity;
use KSP\Core\DTO\User\IUser;

readonly final class Verification implements IJsonEntity {

    public const int REASON_NO_INPUT_DATA_GIVEN = 1;
    public const int REASON_USER_NOT_FOUND      = 2;
    public const int REASON_USER_IS_LOCKED      = 3;
    public const int REASON_OK                  = 4;

    public function __construct(
        private IUser $user,
        private bool  $verified = false,
        private int   $reason = 0
    ) {
    }

    public function isVerified(): bool {
        return $this->verified;
    }

    public function getReason(): int {
        return $this->reason;
    }

    public function getUser(): IUser {
        return $this->user;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'verified' => $this->isVerified(),
            'reason'   => $this->getReason(),
            'user'     => $this->getUser()
        ];
    }

}

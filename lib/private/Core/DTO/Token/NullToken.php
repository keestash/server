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

namespace Keestash\Core\DTO\Token;

use DateTimeInterface;
use Keestash\Core\DTO\User\NullUser;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;

class NullToken implements IToken {

    #[\Override]
    public function getId(): int {
        return 0;
    }

    #[\Override]
    public function getName(): string {
        return '';
    }

    #[\Override]
    public function getValue(): string {
        return '';
    }

    #[\Override]
    public function equals(IToken $token): bool {
        return false;
    }

    #[\Override]
    public function expired(): bool {
        return true;
    }

    #[\Override]
    public function valid(): bool {
        return false;
    }

    #[\Override]
    public function getUser(): IUser {
        return new NullUser();
    }

    #[\Override]
    public function getCreateTs(): DateTimeInterface {
        return new \DateTimeImmutable();
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [];
    }

}

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

namespace KSP\Core\DTO\Token;

use DateTime;
use KSP\Core\DTO\Entity\IJsonObject;
use KSP\Core\DTO\User\IUser;

interface IToken extends IJsonObject {

    public function getId(): int;

    public function getName(): string;

    public function getValue(): string;

    public function equals(IToken $token): bool;

    public function expired(): bool;

    public function valid(): bool;

    public function getUser(): IUser;

    public function getCreateTs(): DateTime;

}

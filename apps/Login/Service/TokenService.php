<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2020> <Dogan Ucar>
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

namespace KSA\Login\Service;

use DateTimeImmutable;
use Keestash\Core\DTO\Token\Token;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;

class TokenService {

    public function generate(string $name, IUser $user): IToken {
        $token = new Token();
        $token->setCreateTs(new DateTimeImmutable());
        $token->setUser($user);
        $token->setValue(bin2hex(random_bytes(16)));
        $token->setName($name);
        return $token;
    }

}

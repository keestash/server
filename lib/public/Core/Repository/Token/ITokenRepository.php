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

namespace KSP\Core\Repository\Token;

use Keestash\Exception\Token\TokenNotCreatedException;
use Keestash\Exception\Token\TokenNotDeletedException;
use Keestash\Exception\Token\TokenNotFoundException;
use KSP\Core\DTO\Token\IToken;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\IRepository;

interface ITokenRepository extends IRepository {

    /**
     * @param IToken $token
     * @return IToken
     * @throws TokenNotCreatedException
     */
    public function add(IToken $token): IToken;

    /**
     * @param string $hash
     * @return IToken
     * @throws TokenNotFoundException
     */
    public function getByValue(string $hash): IToken;

    /**
     * @param IToken $token
     * @return IToken
     * @throws TokenNotDeletedException
     */
    public function remove(IToken $token): IToken;

    /**
     * @param IUser $user
     * @return void
     * @throws TokenNotDeletedException
     */
    public function removeForUser(IUser $user): void;

}

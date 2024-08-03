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

namespace KSP\Core\Repository\User;

use doganoo\PHPAlgorithms\Common\Exception\InvalidKeyTypeException;
use doganoo\PHPAlgorithms\Common\Exception\UnsupportedKeyTypeException;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\State\UserStateNotRemovedException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\DTO\User\IUserState;

/**
 * This methods registers the state for users. If the user gets locked, he is not able to
 * do any actions on Keestash anymore.
 *
 * If the user enters the delete state, he will be deleted after a certain amount of time.
 *
 * Interface IUserStateRepository
 * @package KSP\Core\Repository\User
 */
interface IUserStateRepository {

    /**
     * @param IUser $user
     * @return IUserState
     * @throws InvalidKeyTypeException
     * @throws UnsupportedKeyTypeException
     * @throws UserStateException
     */
    public function getByUser(IUser $user): IUserState;

    public function getByHash(string $hash): IUserState;

    public function insert(IUser $user, string $state, ?string $hash = null): void;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateNotRemovedException
     */
    public function remove(IUser $user): void;

}

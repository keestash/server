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

use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\State\UserStateNotInsertedException;
use Keestash\Exception\User\State\UserStateNotRemovedException;
use KSP\Core\DTO\User\IUser;

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
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     */
    public function lock(IUser $user): void;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function unlock(IUser $user): void;


    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getLockedUsers(): HashTable;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     * TODO check whether already exists
     */
    public function delete(IUser $user): void;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function revertDelete(IUser $user): void;

    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getDeletedUsers(): HashTable;

    /**
     * @param IUser  $user
     * @param string $hash
     * @return void
     * @throws UserStateException
     * @throws UserStateNotInsertedException
     */
    public function requestPasswordReset(IUser $user, string $hash): void;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateException
     * @throws UserStateNotRemovedException
     */
    public function revertPasswordChangeRequest(IUser $user): void;

    /**
     * @return HashTable
     * @throws UserStateException
     */
    public function getUsersWithPasswordResetRequest(): HashTable;

    /**
     * @param IUser $user
     * @return void
     * @throws UserStateNotRemovedException
     */
    public function removeAll(IUser $user): void;


}

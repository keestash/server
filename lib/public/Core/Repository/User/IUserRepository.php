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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Exception\User\UserException;
use Keestash\Exception\User\UserNotCreatedException;
use Keestash\Exception\User\UserNotDeletedException;
use Keestash\Exception\User\UserNotFoundException;
use Keestash\Exception\User\UserNotUpdatedException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\IRepository;

/**
 * Interface IUserRepository
 *
 * @package KSP\Core\Repository\User
 */
interface IUserRepository extends IRepository {

    /**
     * @param string $name
     * @return IUser
     * @throws UserException
     */
    public function getUser(string $name): IUser;

    /**
     * @param string $email
     * @return IUser
     * @throws UserNotFoundException
     */
    public function getUserByEmail(string $email): IUser;

    /**
     * @param string $hash
     * @return IUser
     * @throws UserNotFoundException
     */
    public function getUserByHash(string $hash): IUser;

    /**
     * Returns an instance of IUser or null, if not found
     *
     * @param string $id
     *
     * @return IUser
     * @throws UserNotFoundException
     */
    public function getUserById(string $id): IUser;

    /**
     * Returns a list of users, registered for the app
     *
     * @return ArrayList
     * @throws UserException
     */
    public function getAll(): ArrayList;

    /**
     * @param IUser $user
     * @return IUser
     * @throws UserNotCreatedException
     */
    public function insert(IUser $user): IUser;

    /**
     * @param IUser $user
     * @return IUser
     * @throws UserNotUpdatedException
     *
     * TODO update roles and permissions
     */
    public function update(IUser $user): IUser;

    /**
     * Removes an instance of IUser
     *
     * @param IUser $user
     *
     * @return IUser
     * @throws UserNotDeletedException
     */
    public function remove(IUser $user): IUser;

    /**
     * @param string $name
     * @return ArrayList
     * @throws UserException
     */
    public function searchUsers(string $name): ArrayList;

}

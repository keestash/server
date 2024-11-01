<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2021> <Dogan Ucar>
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

namespace KSP\Core\Service\User\Repository;

use Keestash\Exception\User\State\UserNotLockedException;
use Keestash\Exception\User\UserException;
use Keestash\Exception\User\UserNotCreatedException;
use Keestash\Exception\User\UserNotDeletedException;
use Keestash\Exception\User\UserNotUpdatedException;
use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;

interface IUserRepositoryService {

    /**
     * @param IUser      $user
     * @param IFile|null $file
     * @return IUser
     * @throws UserNotDeletedException
     * @throws UserNotLockedException
     * @throws UserNotCreatedException
     */
    public function createUser(IUser $user, ?IFile $file = null): IUser;

    /**
     * @param IUser $user
     * @return array
     * @throws UserException
     */
    public function removeUser(IUser $user): array;

    /**
     * @param IUser $user
     * @return bool
     */
    public function createSystemUser(IUser $user): bool;

    /**
     * @param string $name
     * @return bool
     */
    public function userExistsByName(string $name): bool;

    /**
     * @param string $id
     * @return bool
     */
    public function userExistsById(string $id): bool;

    /**
     * @param string $email
     * @return bool
     */
    public function userExistsByEmail(string $email): bool;

    /**
     * @param IUser $updatedUser
     * @param IUser $user
     * @return IUser
     * @throws UserNotUpdatedException
     */
    public function updateUser(IUser $updatedUser, IUser $user, string $key): IUser;

}

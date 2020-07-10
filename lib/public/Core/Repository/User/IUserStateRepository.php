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

    // for locking users
    public function lock(IUser $user): bool;

    public function unlock(IUser $user): bool;

    public function isLocked(IUser $user): bool;

    public function getLockedUsers(): HashTable;

    // for marking users as deleted in the app
    public function delete(IUser $user): bool;

    public function revertDelete(IUser $user): bool;

    public function isDeleted(IUser $user): bool;

    public function getDeletedUsers(): HashTable;

    // for password change requests
    public function requestPasswordReset(IUser $user, string $hash): bool;

    public function revertPasswordChangeRequest(IUser $user): bool;

    public function hasPasswordResetRequested(IUser $user): bool;

    public function getUsersWithPasswordResetRequest(): HashTable;

    // public
    public function getAll(): HashTable;

    // for removing from the system
    public function remove(IUser $user, string $state): bool;

    public function removeAll(IUser $user): bool;


}

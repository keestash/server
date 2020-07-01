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
use KSP\Core\DTO\User\IJsonUser;

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

    public function lock(IJsonUser $user): bool;

    public function unlock(IJsonUser $user): bool;

    public function delete(IJsonUser $user): bool;

    public function revertDelete(IJsonUser $user): bool;

    public function getDeletedUsers(): HashTable;

    public function getLockedUsers(): HashTable;

    public function getAll(): HashTable;

    public function remove(IJsonUser $user, string $state): bool;

    public function removeAll(IJsonUser $user): bool;

    public function isLocked(IJsonUser $user): bool;

    public function isDeleted(IJsonUser $user): bool;

}

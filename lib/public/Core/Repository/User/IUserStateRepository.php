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

use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayLists\ArrayList;
use KSP\Core\DTO\IUser;

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

    public function lock(IUser $user): bool;

    public function unlock(IUser $user): bool;

    public function delete(IUser $user): bool;

    public function revertDelete(IUser $user): bool;

    public function getDeletedUsers(): ArrayList;

}

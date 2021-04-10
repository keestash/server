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

use KSP\Core\DTO\File\IFile;
use KSP\Core\DTO\User\IUser;

interface IUserRepositoryService {

    public function removeUser(IUser $user): array;

    public function createSystemUser(IUser $user): bool;

    public function createUser(IUser $user, ?IFile $file = null): IUser;

    public function userExistsByName(string $name): bool;

    public function updateUser(IUser $updatedUser, IUser $oldUser): bool;

}
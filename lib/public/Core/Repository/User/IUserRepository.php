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
use KSP\Core\DTO\User\IJsonUser;
use KSP\Core\Repository\IRepository;

/**
 * Interface IUserRepository
 *
 * @package KSP\Core\Repository\User
 */
interface IUserRepository extends IRepository {

    public function getUser(string $name): ?IJsonUser;

    public function getUserById(string $id): ?IJsonUser;

    public function getAll(): ArrayList;

    public function insert(IJsonUser $user): ?int;

    public function update(IJsonUser $user): bool;

    public function remove(IJsonUser $user): bool;

}

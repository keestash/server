<?php
declare(strict_types=1);

/**
 * Keestash
 *
 * Copyright (C) <2022> <Dogan Ucar>
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

use Keestash\Command\Permission\Add;
use Keestash\Command\Permission\AssignPermissionToRole;
use Keestash\Command\Permission\Get;
use Keestash\Command\Permission\PermissionsByRole;
use Keestash\Command\Role\AssignRoleToUser;
use Keestash\Command\Role\RolesByUser;

return [
    \Keestash\Command\Keestash\Worker::class
    , Get::class
    , \Keestash\Command\Role\Get::class
    , RolesByUser::class
    , PermissionsByRole::class
    , Add::class
    , \Keestash\Command\Role\Add::class
    , AssignRoleToUser::class
    , AssignPermissionToRole::class
];
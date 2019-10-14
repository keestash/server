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

namespace Keestash\Core\Repository\Migration\Base;

use DateTime;
use Phinx\Migration\AbstractMigration;

abstract class KeestashMigration extends AbstractMigration {


    protected function addPermission(string $permissionName, int $roleId) {
        $this->table("permission")
            ->insert(
                [
                    "name" => $permissionName
                ]
            )
            ->save();

        $id = $this->getAdapter()->getConnection()->lastInsertId();

        $this->table("permission_role")
            ->insert(
                [
                    "permission_id" => $id
                    , "role_id"     => $roleId
                ]
            )
            ->save();
    }

}
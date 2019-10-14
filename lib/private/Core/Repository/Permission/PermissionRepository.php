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

namespace Keestash\Core\Repository\Permission;

use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use Keestash\Core\Permission\Permission;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Permission\IPermission;
use KSP\Core\Repository\Permission\IPermissionRepository;
use PDO;

/**
 * Class PermissionManager
 * @package Keestash\Core\Manager\BackendManager
 */
class PermissionRepository extends AbstractRepository implements IPermissionRepository {

    public function getPermissionById(int $id): ?IPermission {
        $permission = null;
        $sql        = "select 
                        `id`
                        , `name`
                from `permission` p 
                where p.id = :id;";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $permission;
        }
        $statement->bindParam("id", $id);
        $statement->execute();
        $permission = new Permission();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id   = $row[0];
            $name = $row[1];
            $permission->setId($id);
            $permission->setName($name);
            $roles = $this->getPermissionRoles($id);
            $permission->setRoles($roles);
        }
        return $permission;
    }

    public function getPermissionRoles(int $id): ?BinarySearchTree {
        $tree = null;
        $sql  = "select r.`id`
                    from `role` r 
                    left join `permission_role` pr on r.`id` = pr.`role_id`
                    left join `permission` p on pr.`permission_id` = p.`id`
                where p.`id` = :id;";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $tree;
        }
        $statement->bindParam("id", $id);
        $statement->execute();
        $tree = new BinarySearchTree();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id = $row[0];
            $tree->insertValue($id);
        }
        return $tree;
    }

    public function getPermission(string $name): ?IPermission {
        $permission = null;
        $sql        = "select 
                        `id`
                        , `name`
                from `permission` p 
                where p.`name` = :name;";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $permission;
        }
        $statement->bindParam("name", $name);
        $statement->execute();
        $permission = new Permission();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id   = $row[0];
            $name = $row[1];
            $permission->setId($id);
            $permission->setName($name);
            $roles = $this->getPermissionRoles($id);
            $permission->setRoles($roles);
        }
        return $permission;
    }

    public function getPermissionsByRole(int $id): ?BinarySearchTree {
        $tree = null;
        $sql  = "select 
                        p.`id`
                        , p.`name`
                    from `permission` p 
                    left join `permission_role` pr on p.`id` = pr.`permission_id`
                    left join `role` r on pr.`permission_id` = r.`id`
                where r.`id` = :id;";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $tree;
        }
        $statement->bindParam("id", $id);
        $statement->execute();
        $tree = new BinarySearchTree();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $permission = new Permission();
            $permission->setId((int) $row[0]);
            $permission->setName((string) $row[1]);
            $tree->insertValue($permission);
        }
        return $tree;
    }

}
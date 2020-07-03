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
use doganoo\SimpleRBAC\Test\DataProvider\Role;
use Keestash\Core\Repository\AbstractRepository;
use KSP\Core\Backend\IBackend;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\Permission\IPermissionRepository;
use KSP\Core\Repository\Permission\IRoleRepository;
use PDO;

/**
 * Class RoleManager
 * @package Keestash\Core\Manager\BackendManager\Permission
 */
class RoleRepository extends AbstractRepository implements IRoleRepository {

    private $permissionManager = null;

    public function __construct(
        IBackend $backend
        , IPermissionRepository $permissionManager
    ) {
        parent::__construct($backend);
        $this->permissionManager = $permissionManager;
    }

    public function getRolesByUser(IUser $user): ?BinarySearchTree {
        $tree = null;
        $sql  = "select 
                    r.`id`
                    , r.`name`
                    , r.`create_ts`
                 from `role` r
                    join `user_role` ur on r.`id` = ur.`role_id`
                 where ur.`user_id` = :user_id
        ";

        $statement = parent::prepareStatement($sql);

        if (null === $statement) {
            return $tree;
        }
        $id = $user->getId();
        $statement->bindParam("user_id", $id);
        $statement->execute();

        $tree = new BinarySearchTree();
        while ($row = $statement->fetch(PDO::FETCH_BOTH)) {
            $id   = $row[0];
            $name = $row[1];

            $role = new Role();
            $role->setId((int) $id);
            $role->setName((string) $name);

            $permissions = $this->permissionManager->getPermissionsByRole($role->getId());
            $role->setPermissions($permissions);
            $tree->insertValue($role);
        }

        return $tree;
    }

    public function removeUserRoles(IUser $user): bool {
        $sql       = "DELETE FROM `user_role` WHERE `user_id` = :user_id;";
        $statement = $this->prepareStatement($sql);

        if (null === $statement) return false;
        $userId = $user->getId();
        $statement->bindParam("user_id", $userId);
        $statement->execute();
        return false === $this->hasErrors($statement->errorCode());
    }

}

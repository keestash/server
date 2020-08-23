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

namespace Keestash\Core\Permission;

use doganoo\PHPAlgorithms\Datastructure\Graph\Tree\BinarySearchTree;
use doganoo\SimpleRBAC\Common\IPermission;
use doganoo\SimpleRBAC\Common\IUser;
use KSP\Core\Permission\IDataProvider;
use KSP\Core\Permission\IRole;
use KSP\Core\Repository\Permission\IPermissionRepository;

class DataProvider implements IDataProvider {

    /** @var null|\KSP\Core\Permission\IUser $user */
    private $user = null;
    /** @var null|IPermissionRepository $permissionManager */
    private $permissionManager = null;

    public function __construct(
        ?\KSP\Core\Permission\IUser $user
        , IPermissionRepository $permissionManager
    ) {
        $this->user              = $user;
        $this->permissionManager = $permissionManager;
    }


    /**
     * the user whose permissions should be validated
     *
     * @return IUser
     */
    public function getUser(): ?IUser {
        return $this->user;
    }

    /**
     * returns the permission object that belongs to $id
     *
     * @param int $id
     *
     * @return IPermission|null
     */
    public function getPermission(int $id): ?IPermission {
        return $this->permissionManager->getPermissionById($id);
    }

    /**
     * all default permissions that are public for all users
     *
     * @return null|BinarySearchTree
     */
    public function getDefaultPermissions(): ?BinarySearchTree {
        return $this->permissionManager->getPermissionsByRole(IRole::ID_PUBLIC);
    }

}

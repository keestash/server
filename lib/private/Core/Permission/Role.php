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
use KSP\Core\Permission\IRole;

class Role implements IRole {

    /** @var null|int $id */
    private $id = null;

    /** @var null|string $name */
    private $name = null;

    /** @var null|BinarySearchTree $permissions */
    private $permissions = null;

    /**
     * @param mixed $object
     * @return int
     */
    public function compareTo($object): int {
        if ($object instanceof IRole) {
            if ($this->getId() < $object->getId()) {
                return -1;
            }
            if ($this->getId() == $object->getId()) {
                return 0;
            }
            if ($this->getId() > $object->getId()) {
                return 1;
            }
            //TODO same name and roles?
        }
        return -1;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void {
        $this->name = $name;
    }

    /**
     * @return BinarySearchTree|null
     */
    public function getPermissions(): ?BinarySearchTree {
        return $this->permissions;
    }

    /**
     * @param BinarySearchTree|null $permissions
     */
    public function setPermissions(?BinarySearchTree $permissions): void {
        $this->permissions = $permissions;
    }

}
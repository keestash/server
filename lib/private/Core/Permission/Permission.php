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
use doganoo\SimpleRBAC\Common\IContext;
use KSP\Core\Permission\IPermission;

/**
 * Class Permission
 * @package Keestash\Core\Permission
 */
class Permission implements IPermission {

    /** @var null|int $id */
    private $id = null;

    /** @var null|string $name */
    private $name = null;

    /** @var null|BinarySearchTree $roles */
    private $roles = null;

    /** @var null|IContext $context */
    private $context = null;

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
    public function getRoles(): ?BinarySearchTree {
        return $this->roles;
    }

    /**
     * @param BinarySearchTree|null $roles
     */
    public function setRoles(?BinarySearchTree $roles): void {
        $this->roles = $roles;
    }

    /**
     * @return IContext|null
     */
    public function getContext(): ?IContext {
        return $this->context;
    }

    /**
     * @param IContext $context
     */
    public function setContext(IContext $context): void {
        $this->context = $context;
    }

    /**
     * @param mixed $object
     * @return int
     */
    public function compareTo($object): int {
        if ($object instanceof IPermission) {
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

}
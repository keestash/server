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

namespace Keestash\Core\DTO\RBAC;

use DateTimeInterface;
use doganoo\PHPAlgorithms\Common\Interfaces\IComparable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Entity\RoleInterface;
use KSP\Core\DTO\RBAC\IRole;

class Role implements IRole {

    public function __construct(private readonly int                 $id, private readonly string            $name, private readonly HashTable         $permissions, private readonly DateTimeInterface $createTs)
    {
    }

    #[\Override]
    public function getId(): int {
        return $this->id;
    }

    #[\Override]
    public function getName(): string {
        return $this->name;
    }

    #[\Override]
    public function getPermissions(): HashTable {
        return $this->permissions;
    }

    #[\Override]
    public function getCreateTs(): DateTimeInterface {
        return $this->createTs;
    }

    #[\Override]
    public function compareTo($object): int {
        if (!$object instanceof RoleInterface) {
            return IComparable::IS_LESS;
        }
        if ($this->getId() < $object->getId()) {
            return IComparable::IS_LESS;
        }
        if ($this->getId() == $object->getId()) {
            return IComparable::EQUAL;
        }
        if ($this->getId() > $object->getId()) {
            return IComparable::IS_GREATER;
        }
        return IComparable::IS_LESS;
    }

    #[\Override]
    public function jsonSerialize(): array {
        return [
            'id'            => $this->getId()
            , 'name'        => $this->getName()
            , 'permissions' => $this->getPermissions()->toArray()
            , 'create_ts'   => $this->getCreateTs()
        ];
    }

}
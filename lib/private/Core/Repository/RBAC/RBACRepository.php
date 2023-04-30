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

namespace Keestash\Core\Repository\RBAC;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use doganoo\SimpleRBAC\Entity\RoleInterface;
use doganoo\SimpleRBAC\Entity\UserInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Core\DTO\RBAC\Permission;
use Keestash\Core\DTO\RBAC\Role;
use Keestash\Exception\KeestashException;
use KSP\Core\Backend\IBackend;

class RBACRepository implements RBACRepositoryInterface {

    private IDateTimeService $dateTimeService;
    private IBackend         $backend;

    public function __construct(
        IBackend           $backend
        , IDateTimeService $dateTimeService
    ) {
        $this->dateTimeService = $dateTimeService;
        $this->backend         = $backend;
    }

    public function getRolesByUser(UserInterface $user): HashTable {
        $roles        = new HashTable();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'r.`id`'
                , 'r.`name`'
                , 'r.`create_ts`'
            ]
        )
            ->from('`role`', 'r')
            ->join('r', '`role_user`', 'ru', 'r.`id` = ru.`role_id`')
            ->where('ru.`user_id` = ?')
            ->setParameter(0, $user->getId());

        $result = $queryBuilder->executeQuery();

        foreach ($result->fetchAllNumeric() as $row) {
            $role = new Role(
                (int) $row[0]
                , (string) $row[1]
                , $this->getPermissionsByRoleId((int) $row[0])
                , $this->dateTimeService->fromFormat((string) $row[2])
            );
            $roles->put((int) $row[0], $role);
        }

        return $roles;
    }

    public function getPermissionsByRoleId(int $roleId): HashTable {
        $permissions  = new HashTable();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'p.`id`'
                , 'p.`name`'
                , 'p.`create_ts`'
            ]
        )
            ->from('`permission`', 'p')
            ->join('p', '`role_permission`', 'rp', 'p.`id` = rp.`permission_id`')
            ->where('rp.`role_id` = ?')
            ->setParameter(0, $roleId);

        $result = $queryBuilder->executeQuery();

        foreach ($result->fetchAllNumeric() as $row) {
            $permission = new Permission(
                (int) $row[0]
                , (string) $row[1]
                , $this->dateTimeService->fromFormat((string) $row[2])
            );
            $permissions->put($permission->getId(), $permission);
        }
        return $permissions;
    }

    public function getAllPermissions(): ArrayList {
        $permissions  = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'p.`id`'
                , 'p.`name`'
                , 'p.`create_ts`'
            ]
        )
            ->from('`permission`', 'p');

        $result = $queryBuilder->executeQuery();

        foreach ($result->fetchAllNumeric() as $row) {
            $permission = new Permission(
                (int) $row[0]
                , (string) $row[1]
                , $this->dateTimeService->fromFormat((string) $row[2])
            );
            $permissions->add($permission);
        }
        return $permissions;
    }

    public function getAllRoles(): ArrayList {
        $roles        = new ArrayList();
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'r.`id`'
                , 'r.`name`'
                , 'r.`create_ts`'
            ]
        )
            ->from('`role`', 'r');

        $result = $queryBuilder->executeQuery();

        foreach ($result->fetchAllNumeric() as $row) {
            $permission = new Role(
                (int) $row[0]
                , (string) $row[1]
                , $this->getPermissionsByRoleId((int) $row[0])
                , $this->dateTimeService->fromFormat((string) $row[2])
            );
            $roles->add($permission);
        }
        return $roles;
    }

    public function getRole(int $roleId): RoleInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'r.`id`'
                , 'r.`name`'
                , 'r.`create_ts`'
            ]
        )
            ->from('`role`', 'r')
            ->where('r.`id` = ?')
            ->setParameter(0, $roleId);

        $result   = $queryBuilder->executeQuery();
        $rows     = $result->fetchAllNumeric();
        $rowCount = count($rows);

        if (0 === $rowCount) {
            return new NullRole();
        }

        return new Role(
            (int) $rows[0][0]
            , (string) $rows[0][1]
            , $this->getPermissionsByRoleId((int) $rows[0][0])
            , $this->dateTimeService->fromFormat((string) $rows[0][2])
        );
    }

    public function getPermission(int $permissionId): PermissionInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'p.`id`'
                , 'p.`name`'
                , 'p.`create_ts`'
            ]
        )
            ->from('`permission`', 'p')
            ->where('p.`id` = ?')
            ->setParameter(0, $permissionId);

        $result   = $queryBuilder->executeQuery();
        $rows     = $result->fetchAllNumeric();
        $rowCount = count($rows);

        if (0 === $rowCount) {
            return new NullPermission();
        }

        return new Permission(
            (int) $rows[0][0]
            , (string) $rows[0][1]
            , $this->dateTimeService->fromFormat((string) $rows[0][2])
        );
    }

    public function getPermissionByName(string $name): PermissionInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'p.`id`'
                , 'p.`name`'
                , 'p.`create_ts`'
            ]
        )
            ->from('`permission`', 'p')
            ->where('p.`name` = ?')
            ->setParameter(0, $name);

        $result   = $queryBuilder->executeQuery();
        $rows     = $result->fetchAllNumeric();
        $rowCount = count($rows);

        if (0 === $rowCount) {
            return new NullPermission();
        }

        return new Permission(
            (int) $rows[0][0]
            , (string) $rows[0][1]
            , $this->dateTimeService->fromFormat((string) $rows[0][2])
        );
    }

    public function getRoleByName(string $name): RoleInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->select(
            [
                'r.`id`'
                , 'r.`name`'
                , 'r.`create_ts`'
            ]
        )
            ->from('`role`', 'r')
            ->where('r.`name` = ?')
            ->setParameter(0, $name);

        $result   = $queryBuilder->executeQuery();
        $rows     = $result->fetchAllNumeric();
        $rowCount = count($rows);
        if (0 === $rowCount) {
            return new NullRole();
        }

        return new Role(
            (int) $rows[0][0]
            , (string) $rows[0][1]
            , new HashTable()
            , $this->dateTimeService->fromFormat((string) $rows[0][2])
        );
    }

    public function createRole(RoleInterface $role): RoleInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('`role`')
            ->values(
                [
                    'id'          => '?'
                    , 'name'      => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $role->getId())
            ->setParameter(1, $role->getName())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS($role->getCreateTs())
            )
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new KeestashException();
        }

        return new Role(
            (int) $lastInsertId
            , $role->getName()
            , new HashTable()
            , $role->getCreateTs()
        );
    }

    public function assignRoleToUser(UserInterface $user, RoleInterface $role): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('`role_user`')
            ->values(
                [
                    'role_id'     => '?'
                    , 'user_id'   => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $role->getId())
            ->setParameter(1, $user->getId())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS($role->getCreateTs())
            )
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new KeestashException();
        }

    }

    public function assignPermissionToRole(PermissionInterface $permission, RoleInterface $role): void {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('`role_permission`')
            ->values(
                [
                    'role_id'         => '?'
                    , 'permission_id' => '?'
                    , 'create_ts'     => '?'
                ]
            )
            ->setParameter(0, $role->getId())
            ->setParameter(1, $permission->getId())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS($role->getCreateTs())
            )
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new KeestashException();
        }

    }

    public function createPermission(PermissionInterface $permission): PermissionInterface {
        $queryBuilder = $this->backend->getConnection()->createQueryBuilder();
        $queryBuilder->insert('`permission`')
            ->values(
                [
                    'id'          => '?'
                    , 'name'      => '?'
                    , 'create_ts' => '?'
                ]
            )
            ->setParameter(0, $permission->getId())
            ->setParameter(1, $permission->getName())
            ->setParameter(2,
                $this->dateTimeService->toYMDHIS($permission->getCreateTs())
            )
            ->executeStatement();

        $lastInsertId = $this->backend->getConnection()->lastInsertId();

        if (false === is_numeric($lastInsertId)) {
            throw new KeestashException();
        }

        return new Permission(
            (int) $lastInsertId
            , $permission->getName()
            , $permission->getCreateTs()
        );

    }

}
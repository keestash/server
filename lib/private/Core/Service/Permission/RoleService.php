<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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

namespace Keestash\Core\Service\Permission;

use DateTimeImmutable;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Entity\RoleInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Core\DTO\RBAC\Role;
use KSP\Core\Service\Permission\IRoleService;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface;

class RoleService implements IRoleService {

    public function __construct(
        private readonly Config                    $config
        , private readonly LoggerInterface         $logger
        , private readonly RBACRepositoryInterface $rbacRepository
    ) {
    }

    #[\Override]
    public function createRoles(): void {
        $roles = $this->config
            ->get(ConfigProvider::PERMISSIONS, new Config([]))
            ->get(ConfigProvider::ROLE_LIST, new Config([]));

        $this->logger->debug('inserting roles', ['count' => $roles->count()]);

        foreach ($roles->toArray() as $id => $name) {
            $roleByName = $this->rbacRepository->getRoleByName($name);
            $roleById   = $this->rbacRepository->getRole($id);

            if (
                ($this->roleExists($roleByName)
                    || $this->roleExists($roleById))
            ) {
                $this->logger->info('role exists, skipping',
                    [
                        'roleByName' => json_encode($roleByName)
                        , 'roleById' => json_encode($roleById)
                    ]
                );
                $this->logger->debug(sprintf('role with name %s / id %s exists, skipping', $name, $id));
                continue;
            }

            $this->rbacRepository->createRole(
                new Role(
                    $id
                    , $name
                    , new HashTable() // there are no roles linked with createRole
                    , new DateTimeImmutable()
                )
            );
            $this->logger->info('role inserted',
                [
                    'roleByName' => json_encode($roleByName)
                    , 'roleById' => json_encode($roleById)
                ]
            );
            $this->logger->debug(sprintf('role with name %s / id %s inserted', $name, $id));
        }
    }

    #[\Override]
    public function recreateRoles(): void {
        $this->rbacRepository->clearRoles();
        $this->createRoles();
    }

    #[\Override]
    public function assignAllRoles(): void {
        $permissionsToRoles =
            $this->config->get(ConfigProvider::PERMISSIONS, new Config([]))
                ->get(ConfigProvider::ROLE_PERMISSION_LIST, new Config([]));

        foreach ($permissionsToRoles->toArray() as $role => $permissions) {
            $roleObject = $this->rbacRepository->getRoleByName($role);
            if ($roleObject instanceof NullRole) {
                $this->logger->debug('role does not exist. Skipping', ['role', $role]);
                continue;
            }
            foreach ($permissions as $permission) {
                $permissionObject = $this->rbacRepository->getPermission($permission);

                if ($permissionObject instanceof NullPermission) {
                    $this->logger->debug('permission does not exist. Skipping', ['role', $role]);
                    continue;
                }

                $this->rbacRepository->assignPermissionToRole(
                    $permissionObject
                    , $roleObject
                );

                $this->logger->debug('permission assigned to role', ['permission' => $permission, 'role', $role]);
            }
        }
    }

    private function roleExists(RoleInterface $permission): bool {
        return !($permission instanceof NullRole);
    }

    #[\Override]
    public function reassignAllRoles(): void {
        $this->rbacRepository->clearRolePermissions();
        $this->assignAllRoles();
    }

}
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

namespace Keestash\Command\Permission\Role;

use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Exception\KeestashException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignPermissionToRole extends KeestashCommand {

    public const string ARGUMENT_NAME_PERMISSION_ID = 'permission-id';
    public const string ARGUMENT_NAME_ROLE_ID       = 'role-id';

    public function __construct(private readonly RBACRepositoryInterface $rbacRepository) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("permission:role:assign")
            ->setDescription("adds a permission to role")
            ->addArgument(
                AssignPermissionToRole::ARGUMENT_NAME_PERMISSION_ID
                , InputArgument::REQUIRED
                , 'the permission id'
            )
            ->addArgument(
                AssignPermissionToRole::ARGUMENT_NAME_ROLE_ID
                , InputArgument::REQUIRED
                , 'the role id'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $permissionId = $input->getArgument(AssignPermissionToRole::ARGUMENT_NAME_PERMISSION_ID);
        $roleId       = $input->getArgument(AssignPermissionToRole::ARGUMENT_NAME_ROLE_ID);

        $permission = $this->rbacRepository->getPermission((int) $permissionId);
        $role       = $this->rbacRepository->getRole((int) $roleId);

        if ($permission instanceof NullPermission) {
            $this->writeError('no permission found', $output);
            return 0;
        }

        if ($role instanceof NullRole) {
            $this->writeError('no role found', $output);
            return 0;
        }

        $permissions = $this->rbacRepository->getPermissionsByRoleId($role->getId());

        if ($permissions->containsKey($permission->getId())) {
            $this->writeError('permission already assigned to role', $output);
            return 0;
        }

        try {
            $this->rbacRepository->assignPermissionToRole($permission, $role);
            $this->writeInfo(
                sprintf(
                    'permission %s assigned to role %s'
                    , $permission->getId()
                    , $role->getId()
                )
                , $output
            );
        } catch (KeestashException) {
            $this->writeError('user could not be assigned', $output);
        }

        return 0;
    }

}

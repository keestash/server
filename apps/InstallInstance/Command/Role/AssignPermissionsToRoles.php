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

namespace KSA\InstallInstance\Command\Role;

use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\ConfigProvider;
use Keestash\Core\DTO\RBAC\NullPermission;
use Keestash\Core\DTO\RBAC\NullRole;
use KSP\Command\IKeestashCommand;
use Laminas\Config\Config;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignPermissionsToRoles extends KeestashCommand {

    public function __construct(
        private readonly Config                    $config
        , private readonly RBACRepositoryInterface $rbacRepository
        , private readonly LoggerInterface         $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("instance:install:role-permission:assign")
            ->setDescription("assigns permissions to roles");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $permissionsToRoles =
            $this->config->get(ConfigProvider::PERMISSIONS, new Config([]))
                ->get(ConfigProvider::ROLE_PERMISSION_LIST, new Config([]));

        foreach ($permissionsToRoles->toArray() as $role => $permissions) {
            $roleObject = $this->rbacRepository->getRoleByName($role);
            if ($roleObject instanceof NullRole) {
                $this->logger->info('role does not exist. Skipping', ['role', $role]);
                $this->writeInfo(sprintf('role %s does not exist. Skipping', $role), $output);
                continue;
            }
            foreach ($permissions as $permission) {
                $permissionObject = $this->rbacRepository->getPermission($permission);
                if ($permissionObject instanceof NullPermission) {
                    $this->logger->info('permission does not exist. Skipping', ['role', $role]);
                    $this->writeInfo(sprintf('permission %s does not exist. Skipping', $role), $output);
                    continue;
                }
                // TODO check whether already assigned
                $this->rbacRepository->assignPermissionToRole(
                    $permissionObject
                    , $roleObject
                );
                $this->logger->info('permission assigned to role', ['permission' => $permission, 'role', $role]);
                $this->writeComment(sprintf('permission %s assigned to %s', $permission, $role), $output);
            }
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
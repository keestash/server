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

namespace Keestash\Command\Role;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use doganoo\SimpleRBAC\Entity\RoleInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Get extends KeestashCommand {

    public const string ARGUMENT_NAME_ROLE_IDENTIFIER = 'role-identifier';

    public function __construct(
        private readonly RBACRepositoryInterface $rbacRepository
        , private readonly IDateTimeService      $dateTimeService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("role:get")
            ->setDescription("lists one, a list or all roles including permissions")
            ->addArgument(
                Get::ARGUMENT_NAME_ROLE_IDENTIFIER
                , InputArgument::IS_ARRAY | InputArgument::OPTIONAL
                , 'one, a list or non permission id'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $roleIdentifier = (array) $input->getArgument(Get::ARGUMENT_NAME_ROLE_IDENTIFIER);
        $allRoles       = $this->rbacRepository->getAllRoles();
        $listLength     = 0;
        $list           = [];
        $tableRows      = [];

        foreach ($roleIdentifier as $permissionId) {
            if (false === is_numeric($permissionId)) {
                continue;
            }
            $list[] = (int) $permissionId;
            $listLength++;
        }
        /** @var RoleInterface $role */
        foreach ($allRoles as $role) {
            if (
                $listLength > 0
                && false === in_array($role->getId(), $list, true)
            ) {
                continue;
            }

            $permissions = [];
            /** @var PermissionInterface $permission */
            foreach ($role->getPermissions()->toArray() as $permission) {
                $permissions[] = (
                [
                    'id'         => $permission->getId()
                    , 'name'     => $permission->getName()
                    , 'createTs' => $this->dateTimeService->toDMYHIS(
                    $permission->getCreateTs()
                )
                ]
                );
            }
            $tableRows[] = [
                $role->getId()
                , $role->getName()
                , json_encode($permissions, JSON_PRETTY_PRINT)
                , $this->dateTimeService->toDMYHIS(
                    $role->getCreateTs()
                )
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Permissions', 'Create Ts'])
            ->setRows($tableRows);
        $table->render();
        return 0;
    }

}

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

namespace Keestash\Command\Permission;

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\SimpleRBAC\Entity\PermissionInterface;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class PermissionsByRole extends KeestashCommand {

    public const string ARGUMENT_NAME_ROLE_ID = 'role-id';

    public function __construct(
        private readonly RBACRepositoryInterface $rbacRepository
        , private readonly IDateTimeService      $dateTimeService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("permission:role:get")
            ->setDescription("lists all permissions by a given role")
            ->addArgument(
                PermissionsByRole::ARGUMENT_NAME_ROLE_ID
                , InputArgument::REQUIRED
                , 'the role id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $roleId      = $input->getArgument(PermissionsByRole::ARGUMENT_NAME_ROLE_ID);
        $role        = $this->rbacRepository->getRole((int) $roleId);
        $permissions = $this->rbacRepository->getPermissionsByRoleId((int) $roleId);
        $tableRows   = [];

        if (0 === $permissions->size()) {
            $this->writeError('no permissions found', $output);
            return 0;
        }
        /** @var PermissionInterface $permission */
        foreach ($permissions->toArray() as $permission) {
            $tableRows[] = [
                $permission->getId()
                , $permission->getName()
                , $this->dateTimeService->toDMYHIS(
                    $permission->getCreateTs()
                )
            ];
        }

        $this->writeComment(
            sprintf('All permissions for role %s with ID %s'
                , $role->getName()
                , $role->getId()
            )
            , $output
        );

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Create Ts'])
            ->setRows($tableRows);
        $table->render();
        return 0;
    }

}

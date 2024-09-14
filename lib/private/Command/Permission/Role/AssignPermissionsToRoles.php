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

namespace Keestash\Command\Permission\Role;

use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Permission\IRoleService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class AssignPermissionsToRoles extends KeestashCommand {

    public const string OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly IRoleService $roleService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("permission:role:assign-all")
            ->setDescription("assigns permissions to roles")
            ->addOption(
                AssignPermissionsToRoles::OPTION_NAME_FORCE
                , null
                , InputOption::VALUE_NONE
                , 'whether to force recreation'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force = $input->getOption(AssignPermissionsToRoles::OPTION_NAME_FORCE);
        if (true === $force) {
            $this->roleService->reassignAllRoles();
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }
        $this->roleService->assignAllRoles();
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}

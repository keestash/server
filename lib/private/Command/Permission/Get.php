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

class Get extends KeestashCommand {

    public const ARGUMENT_NAME_PERMISSION_IDENTIFIER = 'permission-identifier';

    private RBACRepositoryInterface $rbacRepository;
    private IDateTimeService        $dateTimeService;

    public function __construct(
        RBACRepositoryInterface $rbacRepository
        , IDateTimeService      $dateTimeService
    ) {
        parent::__construct();
        $this->rbacRepository  = $rbacRepository;
        $this->dateTimeService = $dateTimeService;
    }

    protected function configure(): void {
        $this->setName("permission:get")
            ->setDescription("lists one, a list or all permissions")
            ->addArgument(
                Get::ARGUMENT_NAME_PERMISSION_IDENTIFIER
                , InputArgument::IS_ARRAY | InputArgument::OPTIONAL
                , 'one, a list or non permission id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $permissionIdentifier = $input->getArgument(Get::ARGUMENT_NAME_PERMISSION_IDENTIFIER);
        $allPermissions       = $this->rbacRepository->getAllPermissions();
        $list                 = [];
        $listLength           = 0;
        $tableRows            = [];

        foreach ($permissionIdentifier as $permissionId) {
            if (false === is_numeric($permissionId)) {
                continue;
            }
            $list[] = (int) $permissionId;
            $listLength++;
        }
        /** @var PermissionInterface $permission */
        foreach ($allPermissions as $permission) {
            if (
                $listLength > 0
                && false === in_array($permission->getId(), $list, true)
            ) {
                continue;
            }
            $tableRows[] = [
                $permission->getId()
                , $permission->getName()
                , $this->dateTimeService->toDMYHIS(
                    $permission->getCreateTs()
                )
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Create Ts'])
            ->setRows($tableRows);
        $table->render();
        return 0;
    }

}
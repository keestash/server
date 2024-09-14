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

use DateTimeImmutable;
use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Table\HashTable;
use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Core\DTO\RBAC\Role;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Add extends KeestashCommand {

    public function __construct(
        private readonly RBACRepositoryInterface $rbacRepository
        , private readonly IDateTimeService      $dateTimeService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("role:add")
            ->setDescription("add a new role");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {

        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a role");
        $name = $style->ask("Name") ?? "";

        $role = $this->rbacRepository->getRoleByName((string) $name);

        if (false === ($role instanceof NullRole)) {
            $this->writeError('role with this name already exists', $output);
            return 0;
        }

        $newRole = new Role(
            0
            , $name
            , new HashTable()
            , new DateTimeImmutable()
        );

        $newRole = $this->rbacRepository->createRole($newRole);

        $this->writeInfo('role created', $output);
        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Create Ts'])
            ->setRows(
                [
                    [$newRole->getId()
                     , $newRole->getName()
                     , $this->dateTimeService->toDMYHIS(
                        $newRole->getCreateTs()
                    )]
                ]
            );
        $table->render();
        return 0;
    }

}
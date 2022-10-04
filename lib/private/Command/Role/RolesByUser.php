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
use doganoo\SimpleRBAC\Entity\RoleInterface;
use Keestash\Command\KeestashCommand;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Repository\User\IUserRepository;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RolesByUser extends KeestashCommand {

    public const ARGUMENT_NAME_USER_ID = 'user-id';

    private IUserRepository  $userRepository;
    private IDateTimeService $dateTimeService;

    public function __construct(
        IUserRepository    $userRepository
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct();
        $this->userRepository  = $userRepository;
        $this->dateTimeService = $dateTimeService;
    }

    protected function configure(): void {
        $this->setName("role:user:get")
            ->setDescription("lists all roles by a given user")
            ->addArgument(
                RolesByUser::ARGUMENT_NAME_USER_ID
                , InputArgument::REQUIRED
                , 'the user id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId    = $input->getArgument(RolesByUser::ARGUMENT_NAME_USER_ID);
        $tableRows = [];

        try {
            $user = $this->userRepository->getUserById((string) $userId);
        } catch (UserNotFoundException $exception) {
            $this->writeError('no user found', $output);
            return 0;
        }

        /** @var RoleInterface $role */
        foreach ($user->getRoles()->toArray() as $role) {
            $tableRows[] = [
                $role->getId()
                , $role->getName()
                , $this->dateTimeService->toDMYHIS(
                    $role->getCreateTs()
                )
            ];
        }

        $this->writeComment(
            sprintf('All roles for user %s with ID %s'
                , $user->getName()
                , $user->getId()
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
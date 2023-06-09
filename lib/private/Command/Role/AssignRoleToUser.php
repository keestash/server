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

use doganoo\SimpleRBAC\Repository\RBACRepositoryInterface;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\RBAC\NullRole;
use Keestash\Exception\KeestashException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Repository\User\IUserRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AssignRoleToUser extends KeestashCommand {

    public const ARGUMENT_NAME_USER_ID = 'user-id';
    public const ARGUMENT_NAME_ROLE_ID = 'role-id';

    private IUserRepository         $userRepository;
    private RBACRepositoryInterface $rbacRepository;

    public function __construct(
        IUserRepository           $userRepository
        , RBACRepositoryInterface $rbacRepository
    ) {
        parent::__construct();
        $this->userRepository = $userRepository;
        $this->rbacRepository = $rbacRepository;
    }

    protected function configure(): void {
        $this->setName("keestash:role:assign-user")
            ->setDescription("adds a user to role")
            ->addArgument(
                AssignRoleToUser::ARGUMENT_NAME_USER_ID
                , InputArgument::REQUIRED
                , 'the user id'
            )
            ->addArgument(
                AssignRoleToUser::ARGUMENT_NAME_ROLE_ID
                , InputArgument::REQUIRED
                , 'the role id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = $input->getArgument(AssignRoleToUser::ARGUMENT_NAME_USER_ID);
        $roleId = $input->getArgument(AssignRoleToUser::ARGUMENT_NAME_ROLE_ID);

        try {
            $user = $this->userRepository->getUserById((string) $userId);
        } catch (UserNotFoundException $exception) {
            $this->writeError('no user found', $output);
            return 0;
        }
        $role = $this->rbacRepository->getRole((int) $roleId);

        if ($role instanceof NullRole) {
            $this->writeError('no role found', $output);
            return 0;
        }

        $roles = $this->rbacRepository->getRolesByUser($user);

        if ($roles->containsKey($role->getId())) {
            $this->writeError('user already has the role', $output);
            return 0;
        }

        try {
            $this->rbacRepository->assignRoleToUser($user, $role);
            $this->writeInfo(
                sprintf(
                    'user %s assigned to role %s'
                    , $user->getId()
                    , $role->getId()
                )
                , $output
            );
        } catch (KeestashException $exception) {
            $this->writeError('user could not be assigned', $output);
        }

        return 0;
    }

}
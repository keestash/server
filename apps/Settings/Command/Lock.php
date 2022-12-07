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

namespace KSA\Settings\Command;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\User\State\UserStateException;
use Keestash\Exception\User\State\UserStateNotInsertedException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Lock extends KeestashCommand {

    public const ARGUMENT_NAME_USER_ID = 'user-id';

    private IUserRepository      $userRepository;
    private IUserStateRepository $userStateRepository;

    public function __construct(
        IUserRepository        $userRepository
        , IUserStateRepository $userStateRepository
    ) {
        parent::__construct();
        $this->userRepository      = $userRepository;
        $this->userStateRepository = $userStateRepository;
    }

    protected function configure(): void {
        $this->setName("users:lock")
            ->setDescription("locks one or more users")
            ->addArgument(
                Lock::ARGUMENT_NAME_USER_ID
                , InputArgument::IS_ARRAY
                , 'the user id(s) or none to list all'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userIds = (array) $input->getArgument(Lock::ARGUMENT_NAME_USER_ID);

        foreach ($userIds as $id) {
            try {
                $user = $this->userRepository->getUserById((string) $id);
                $this->userStateRepository->lock($user);
                $this->writeInfo('locked user ' . $user->getName() . ' (' . $user->getId() . ')', $output);
            } catch (UserNotFoundException $e) {
                $this->writeError('user with id ' . $id . ' not found', $output);
            } catch (UserStateNotInsertedException $e) {
                $this->writeError('user state for user with id ' . $id . ' not found', $output);
            } catch (UserStateException $e) {
                $this->writeError('user state exception', $output);
            }
        }

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
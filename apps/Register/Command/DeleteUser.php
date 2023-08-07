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

namespace KSA\Register\Command;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\User\UserException;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CreateUser
 *
 * @package KSA\Register\Command
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class DeleteUser extends KeestashCommand {

    public const ARGUMENT_NAME_USER_ID = 'user_id';
    public const OPTION_NAME_FORCE     = 'force';

    private IUserRepositoryService $userRepositoryService;
    private IUserRepository        $userRepository;

    public function __construct(
        IUserRepositoryService $userRepositoryService
        , IUserRepository      $userRepository
    ) {
        parent::__construct();
        $this->userRepositoryService = $userRepositoryService;
        $this->userRepository        = $userRepository;
    }

    protected function configure(): void {
        $this->setName("register:delete-user")
            ->setDescription("delete a new user")
            ->addArgument(
                DeleteUser::ARGUMENT_NAME_USER_ID
                , InputArgument::REQUIRED
                , 'the user id to delete'
            )
            ->addOption(
                DeleteUser::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_NONE
                , 'whether questions should be asked'
            );
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws UserException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int {

        $userId    = (string) $input->getArgument(DeleteUser::ARGUMENT_NAME_USER_ID);
        $yes       = $input->getOption(DeleteUser::OPTION_NAME_FORCE);
        $confirmed = $yes;

        if (false === $yes) {
            $confirmed = $this->confirmQuestion(
                'do you really want to delete this user permanently?'
                , $input
                , $output
                , false
            );
        }

        if (false === $confirmed) {
            $this->writeInfo('aborting', $output);
            return 0;
        }

        if (false === $yes) {
            $confirmed = $this->confirmQuestion(
                'do you really want to delete this user permanently?'
                , $input
                , $output
                , false
            );

        }
        if (false === $confirmed) {
            $this->writeInfo('aborting', $output);
            return 0;
        }

        try {
            $user = $this->userRepository->getUserById($userId);
        } catch (UserNotFoundException $exception) {
            $this->writeError('no user found for ' . $userId, $output);
            return 1;
        }
        $this->userRepositoryService->removeUser($user);

        $this->writeInfo("{$user->getName()} delete result: ", $output);
        return 0;
    }

}

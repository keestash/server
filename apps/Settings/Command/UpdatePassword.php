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
use KSA\Settings\Exception\SettingsException;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class UpdatePassword extends KeestashCommand {

    public const OPTION_NAME_WITH_EVENTS = 'with-events';

    private IUserRepository        $userRepository;
    private IUserService           $userService;
    private IUserRepositoryService $userRepositoryService;

    public function __construct(
        IUserRepository          $userRepository
        , IUserService           $userService
        , IUserRepositoryService $userRepositoryService
    ) {
        parent::__construct();

        $this->userRepository        = $userRepository;
        $this->userService           = $userService;
        $this->userRepositoryService = $userRepositoryService;
    }

    protected function configure(): void {
        $this->setName("users:user:change-password")
            ->setDescription("changes a password for a given user")
            ->addOption(
                UpdatePassword::OPTION_NAME_WITH_EVENTS
                , 'w'
                , InputOption::VALUE_NONE
                , 'wheter all events hanging on update user should be triggered'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $userId         = (string) ($style->ask("UserId") ?? "");
        $password       = (string) ($style->askHidden("Password") ?? "");
        $passwordRepeat = (string) ($style->askHidden("Password Repeat") ?? "");
        $withEvents     = (bool) $input->getOption(UpdatePassword::OPTION_NAME_WITH_EVENTS);

        $user = $this->userRepository->getUserById($userId);

        if (null === $user) {
            throw new SettingsException('no user found for ' . $userId);
        }

        if (
            $user->getId() === IUser::SYSTEM_USER_ID
            || $user->getName() === IUser::DEMO_USER_NAME
        ) {
            throw new SettingsException('cannot update password for system user or demo user');
        }

        if ($password !== $passwordRepeat) {
            throw new SettingsException('passwords do not match');
        }

        $newUser = $user;
        if (true === $withEvents) {
            $newUser = clone $user;
        }

        $requirementsMet = $this->userService->passwordHasMinimumRequirements($password);

        if (false === $requirementsMet) {
            throw new SettingsException('the minimum requirements for passwords are not met');
        }

        $newUser->setPassword(
            $this->userService->hashPassword($password)
        );

        $updated = $this->userRepositoryService->updateUser(
            $newUser
            , $user
        );

        $this->writeInfo('Password Updated: ' . ($updated ? 'true' : 'false'), $output);

        return KeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
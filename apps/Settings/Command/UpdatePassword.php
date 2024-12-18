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
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\User\IUserService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

final class UpdatePassword extends KeestashCommand {

    public function __construct(
        private readonly IUserRepository          $userRepository
        , private readonly IUserService           $userService
        , private readonly IUserRepositoryService $userRepositoryService
        , private readonly IUserKeyRepository     $userKeyRepository
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("users:user:change-password")
            ->setDescription("changes the password for a given user");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $userId         = (string) ($style->ask("UserId") ?? "");
        $password       = (string) ($style->askHidden("Password") ?? "");
        $passwordRepeat = (string) ($style->askHidden("Password Repeat") ?? "");

        $user = $this->userRepository->getUserById($userId);

        if (
            $user->getId() === IUser::SYSTEM_USER_ID
            || $user->getName() === IUser::DEMO_USER_NAME
        ) {
            throw new SettingsException('cannot update password for system user or demo user');
        }

        if ($password !== $passwordRepeat) {
            throw new SettingsException('passwords do not match');
        }

        $newUser = clone $user;

        $requirementsMet = $this->userService->passwordHasMinimumRequirements($password);

        if (false === $requirementsMet) {
            throw new SettingsException('the minimum requirements for passwords are not met');
        }

        $newUser->setPassword(
            $this->userService->hashPassword($password)
        );

        $key = $this->userKeyRepository->getKey($user);
        $this->userRepositoryService->updateUser(
            $newUser
            , $user
            , base64_decode($key->getSecret())
        );

        $this->writeInfo('Password Updated', $output);

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}

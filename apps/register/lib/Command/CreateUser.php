<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2019> <Dogan Ucar>
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

use DateTime;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\User\User;
use Keestash\Core\Service\User\UserService;
use KSA\Register\Exception\CreateUserException;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Validation\IValidationService;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class CreateUser extends KeestashCommand {

    /** @var IUserRepository */
    private $userRepository;

    /** @var UserService */
    private $userService;

    /** @var IValidationService */
    private $validationService;

    public function __construct(
        IUserRepository $userRepository
        , UserService $userService
        , IValidationService $validationService
    ) {
        parent::__construct(null);

        $this->userRepository    = $userRepository;
        $this->userService       = $userService;
        $this->validationService = $validationService;
    }

    protected function configure() {
        $this->setName("register:create-user")
            ->setDescription("creates a new user")
            ->addOption('locked', 'l', InputOption::VALUE_OPTIONAL, "whether the user is locked", false)
            ->addOption('deleted', 'd', InputOption::VALUE_OPTIONAL, "whether the user is deleted", false);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws CreateUserException
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a user");
        $name           = $style->ask("Username") ?? "";
        $password       = $style->askHidden("Password") ?? "";
        $passwordRepeat = $style->askHidden("Password Repeat") ?? "";
        $firstName      = $style->ask("First Name") ?? "";
        $lastName       = $style->ask("Last Name") ?? "";
        $email          = $style->ask("Email") ?? "";
        $phone          = $style->ask("Phone") ?? "";
        $website        = $style->ask("Website") ?? "";
        $locked         = $input->getOption('locked') ?? false;
        $deleted        = $input->getOption('deleted') ?? false;

        if ("" === $password || $password !== $passwordRepeat) {
            throw new CreateUserException("passwords are empty or do not match");
        }

        $user = new User();
        $user->setName($name);
        $user->setPassword(
            $this->userService->hashPassword($password)
        );
        $user->setFirstName($firstName);
        $user->setLastName($lastName);
        $user->setEmail($email);
        $user->setPhone($phone);
        $user->setWebsite($website);
        $user->setLocked($locked !== false);
        $user->setHash(
            $this->userService->getRandomHash()
        );
        $user->setCreateTs(new DateTime());
        $user->setDeleted($deleted !== false);

        $errors     = $this->validationService->validate($user);
        $errorCount = count($errors);

        if ($errorCount > 0) {
            foreach ($errors as $error) {
                $this->writeError($error, $output);
            }
            return -1;
        }

        $created = $this->userService->createUser(
            $user
            , $locked
            , $deleted
        );
        if (true === $created) {
            $this->writeInfo("$name created", $output);
            return;
        }
        $this->writeError("Could not create user $name", $output);
    }

}

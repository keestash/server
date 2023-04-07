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

use DateTimeImmutable;
use Exception;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Core\Service\User\UserService;
use Keestash\Exception\KeestashException;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSA\Register\Exception\CreateUserException;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\Repository\IUserRepositoryService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class CreateUser
 *
 * @package KSA\Register\Command
 * @author  Dogan Ucar <dogan@dogan-ucar.de>
 */
class CreateUser extends KeestashCommand {

    public function __construct(
        private readonly UserService              $userService
        , private readonly IUserRepositoryService $userRepositoryService
        , private readonly IEventService          $eventService
        , private readonly IDerivationRepository  $derivationRepository
        , private readonly IDerivationService     $derivationService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("register:create-user")
            ->setDescription("creates a new user")
            ->addOption('locked', 'l', InputOption::VALUE_NONE, "whether the user is locked")
            ->addOption('deleted', 'd', InputOption::VALUE_NONE, "whether the user is deleted");
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void
     * @throws CreateUserException|KeestashException
     */
    protected function execute(InputInterface $input, OutputInterface $output) {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a user");
        $name           = (string) $style->ask("Username") ?? "";
        $password       = $style->askHidden("Password") ?? "";
        $passwordRepeat = $style->askHidden("Password Repeat") ?? "";
        $firstName      = $style->ask("First Name") ?? "";
        $lastName       = $style->ask("Last Name") ?? "";
        $email          = $style->ask("Email") ?? "";
        $phone          = $style->ask("Phone") ?? "";
        $website        = $style->ask("Website") ?? "";
        $locked         = $input->getOption('locked') ?? false;
        $deleted        = $input->getOption('deleted') ?? false;

        $this->userService->validatePasswords((string) $password, (string) $passwordRepeat);

        $user   = $this->userService->toNewUser(
            [
                'user_name'    => $name
                , 'email'      => $email
                , 'last_name'  => $lastName
                , 'first_name' => $firstName
                , 'password'   => $password
                , 'phone'      => $phone
                , 'website'    => $website
                , 'locked'     => $locked !== false
                , 'deleted'    => $deleted !== false
            ]
        );
        $result = $this->userService->validateNewUser($user);

        if ($result->length() > 0) {
            throw new KeestashException(
                (string) json_encode($result->toArray(), JSON_THROW_ON_ERROR)
            );
        }

        try {
            $this->userRepositoryService->createUser($user);
        } catch (Exception $exception) {
            $this->writeError("Could not create user $name", $output);
            $this->writeError($exception->getMessage() . " " . $exception->getTraceAsString(), $output);
            return 1;
        }

        $this->derivationRepository->clear($user);
        $this->derivationRepository->add(
            new Derivation(
                Uuid::uuid4()->toString()
                , $user
                , $this->derivationService->derive($user->getPassword())
                , new DateTimeImmutable()
            )
        );

        $this->eventService->execute(
            new UserRegistrationConfirmedEvent(
                $user
            )
        );

        $this->writeInfo("$name created", $output);
        return 0;
    }

}

<?php
declare(strict_types=1);
/**
 * Keestash
 *
 * Copyright (C) <2023> <Dogan Ucar>
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
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Event\IEventService;
use KSP\Core\Service\User\IUserStateService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateUser extends KeestashCommand {

    public function __construct(
        private readonly IUserRepository     $userRepository
        , private readonly LoggerInterface   $logger
        , private readonly IEventService     $eventService
        , private readonly IUserStateService $userStateService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("register:user:activate")
            ->setDescription("activates a given user")
            ->addArgument('userId', InputArgument::REQUIRED, "whether the user is locked");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = (string) $input->getArgument('userId');
        $user   = $this->userRepository->getUserById($userId);

        if (false === $user->isLocked()) {
            $this->logger->warning('user was not locked', ['userId' => $user->getId()]);
            return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
        }

        $this->userStateService->clear($user);

        $this->eventService->execute(
            new UserRegistrationConfirmedEvent($user, 1)
        );

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}

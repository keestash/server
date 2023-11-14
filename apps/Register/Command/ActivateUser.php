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

use DateTimeImmutable;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Exception\User\State\UserStateException;
use KSA\Register\Event\UserRegistrationConfirmedEvent;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Repository\User\IUserStateRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\Event\IEventService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ActivateUser extends KeestashCommand {

    public function __construct(
        private readonly IUserRepository         $userRepository
        , private readonly IUserStateRepository  $userStateRepository
        , private readonly IDerivationRepository $derivationRepository
        , private readonly IDerivationService    $derivationService
        , private readonly LoggerInterface       $logger
        , private readonly IEventService         $eventService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("register:user:activate")
            ->setDescription("activates a given user")
            ->addArgument('userId', InputArgument::REQUIRED, "whether the user is locked");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = (string) $input->getArgument('userId');
        $user   = $this->userRepository->getUserById($userId);

        try {
            $this->userStateRepository->unlock($user);
        } catch (UserStateException $exception) {
            $this->logger->warning('user was not locked', ['userId' => $user->getId(), 'exception' => $exception]);
        }

        $this->derivationRepository->clear($user);
        $derivation = new Derivation(
            Uuid::uuid4()->toString()
            , $user
            , $this->derivationService->derive($user->getPassword())
            , new DateTimeImmutable()
        );

        $this->logger->info(
            'derivation result webhook'
            , [
                'id'         => $derivation->getId()
                , 'user'     => $derivation->getKeyHolder()
                , 'derived'  => $derivation->getDerived()
                , 'createTs' => $derivation->getCreateTs()
            ]
        );
        $this->derivationRepository->add($derivation);

        $this->eventService->execute(
            new UserRegistrationConfirmedEvent($user,1)
        );

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
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

namespace Keestash\Command\Derivation;

use DateTimeImmutable;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Derivation\Derivation;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddDerivation extends KeestashCommand {

    public function __construct(
        private readonly IDerivationRepository $derivationRepository
        , private readonly IUserRepository     $userRepository
        , private readonly IDerivationService  $derivationService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("derivation:add")
            ->setDescription("adds a new derivation for a user");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = $this->askQuestion('user id', $input, $output);

        $user = $this->userRepository->getUserById($userId);
        $this->derivationRepository->clear($user);
        $this->derivationRepository->add(
            new Derivation(
                Uuid::uuid4()->toString()
                , $user
                , $this->derivationService->derive($user->getPassword())
                , new DateTimeImmutable()
            )
        );
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
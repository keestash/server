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

use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearDerivation extends KeestashCommand {

    public const string ARGUMENT_NAME_USER_ID = 'user-id';

    public function __construct(
        private readonly IDerivationRepository $derivationRepository
        , private readonly IUserRepository     $userRepository
        , private readonly ITokenRepository    $tokenRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("derivation:clear")
            ->setDescription("clears a key derivation for a given user")
            ->addArgument(
                ClearDerivation::ARGUMENT_NAME_USER_ID
                , InputArgument::OPTIONAL
                , 'the user id'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = $input->getArgument(ClearDerivation::ARGUMENT_NAME_USER_ID);

        if (null !== $userId) {
            $user = $this->userRepository->getUserById((string) $userId);
            $this->derivationRepository->clear($user);
            $this->tokenRepository->removeForUser($user);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $this->derivationRepository->clearAll();
        $this->tokenRepository->removeAll();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}

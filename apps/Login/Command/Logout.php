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

namespace KSA\Login\Command;

use Exception;
use Keestash\Command\KeestashCommand;
use Keestash\Exception\User\UserNotFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Logout extends KeestashCommand {

    public function __construct(
        private readonly IUserRepository         $userRepository
        , private readonly LoggerInterface       $logger
        , private readonly ITokenRepository      $tokenRepository
        , private readonly IDerivationRepository $derivationRepository
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName("login:logout")
            ->setDescription("logs in a user");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $userName = (string) ($style->ask("username") ?? "");

        try {
            $user = $this->userRepository->getUser($userName);
        } catch (UserNotFoundException $exception) {
            $this->logger->error('error retrieving user', ['exception' => $exception, 'userName' => $userName]);
            throw new Exception("No User Found");
        }

        $this->tokenRepository->removeForUser($user);
        $this->derivationRepository->clear($user);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
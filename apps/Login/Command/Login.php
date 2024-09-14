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

use DateTimeImmutable;
use Exception;
use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Derivation\Derivation;
use Keestash\Exception\User\UserNotFoundException;
use KSA\Login\Service\TokenService;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\Derivation\IDerivationRepository;
use KSP\Core\Repository\LDAP\IConnectionRepository;
use KSP\Core\Repository\Token\ITokenRepository;
use KSP\Core\Repository\User\IUserRepository;
use KSP\Core\Service\Derivation\IDerivationService;
use KSP\Core\Service\LDAP\ILDAPService;
use KSP\Core\Service\User\IUserService;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Login extends KeestashCommand {

    public function __construct(
        private readonly IUserRepository         $userRepository
        , private readonly LoggerInterface       $logger
        , private readonly IUserService          $userService
        , private readonly ILDAPService          $ldapService
        , private readonly IConnectionRepository $connectionRepository
        , private readonly TokenService          $tokenService
        , private readonly ITokenRepository      $tokenRepository
        , private readonly IDerivationRepository $derivationRepository
        , private readonly IDerivationService    $derivationService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("login:login")
            ->setDescription("logs in a user");
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $style = new SymfonyStyle($input, $output);
        $style->title("Please provide the data required to create a credential");
        $userName = (string) ($style->ask("username") ?? "");
        $password = (string) ($style->askHidden("password") ?? "");

        try {
            $user = $this->userRepository->getUser($userName);
        } catch (UserNotFoundException $exception) {
            $this->logger->error('error retrieving user', ['exception' => $exception, 'userName' => $userName]);
            throw new UserNotFoundException("No User Found");
        }

        if (true === $this->userService->isDisabled($user)) {
            throw new UserNotFoundException("No User Found");
        }

        $verified = false;
        if (true === $user->isLdapUser()) {
            $verified = $this->ldapService->verifyUser(
                $user
                , $this->connectionRepository->getConnectionByUser($user)
                , $password
            );
        } else {
            $this->logger->debug('verifying regular user');
            $verified = $this->userService->verifyPassword($password, $user->getPassword());
        }

        if (false === $verified) {
            $this->logger->warning(
                'user is not verified'
                , [
                    'isLdap' => $user->isLdapUser()
                ]
            );
            $this->writeError('Invalid credentials', $output);
            return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
        }

        $token = $this->tokenService->generate("login", $user);
        $this->tokenRepository->add($token);

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
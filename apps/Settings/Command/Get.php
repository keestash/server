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

use doganoo\DI\DateTime\IDateTimeService;
use doganoo\PHPAlgorithms\Datastructure\Lists\ArrayList\ArrayList;
use Keestash\Command\KeestashCommand;
use KSA\PasswordManager\Exception\KeyNotFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\User\IUser;
use KSP\Core\Repository\EncryptionKey\User\IUserKeyRepository;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Get extends KeestashCommand {

    public const string ARGUMENT_NAME_USER_ID = 'user-id';

    public function __construct(
        private readonly IUserRepository      $userRepository
        , private readonly IUserKeyRepository $userKeyRepository
        , private readonly LoggerInterface    $logger
        , private readonly IDateTimeService   $dateTimeService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("users:list")
            ->setDescription("lists one or all users")
            ->addArgument(
                Get::ARGUMENT_NAME_USER_ID
                , InputArgument::OPTIONAL | InputArgument::IS_ARRAY
                , 'the user id(s) or none to list all'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userIds   = (array) $input->getArgument(Get::ARGUMENT_NAME_USER_ID);
        $userList  = new ArrayList();
        $tableRows = [];

        if ([] === $userIds) {
            $userList = $this->userRepository->getAll();
        } else {
            foreach ($userIds as $id) {
                $userList->add(
                    $this->userRepository->getUserById((string) $id)
                );
            }
        }

        /** @var IUser $user */
        foreach ($userList as $user) {
            try {
                $key = $this->userKeyRepository->getKey($user);
            } catch (KeyNotFoundException $exception) {
                $this->logger->info('no key found for user', ['exception' => $exception, 'user' => $user]);
                $key = null;
            }
            $tableRows[] = [
                $user->getId()
                , $user->getName()
                , $user->getEmail()
                , $user->getHash()
                , $user->isDeleted()
                , $user->isLocked()
                , null !== $key
                , $this->dateTimeService->toDMYHIS($user->getCreateTs())
            ];
        }
        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'E-Mail', 'Hash', 'Deleted', 'Locked', 'Key Exists', 'Created'])
            ->setRows($tableRows);
        $table->render();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
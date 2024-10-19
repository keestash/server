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

namespace KSA\PasswordManager\Command\Node;

use Keestash\Command\KeestashCommand;
use Keestash\Exception\User\UserNotFoundException;
use KSA\PasswordManager\Entity\Edge\Edge;
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Folder\Root;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSP\Command\IKeestashCommand;
use KSP\Core\Repository\User\IUserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DumpAll extends KeestashCommand {

    public const string ARGUMENT_NAME_USER_ID = 'userId';

    public function __construct(
        private readonly NodeRepository    $nodeRepository
        , private readonly LoggerInterface $logger
        , private readonly IUserRepository $userRepository
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("password-manager:dump-all")
            ->setDescription("shows all passwords for the given user")
            ->addArgument(
                DumpAll::ARGUMENT_NAME_USER_ID
                , InputArgument::REQUIRED
                , 'The user id to dump'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $userId = $input->getArgument(DumpAll::ARGUMENT_NAME_USER_ID);

        try {
            $user = $this->userRepository->getUserById((string) $userId);
            $root = $this->nodeRepository->getRootForUser($user);
        } catch (PasswordManagerException|UserNotFoundException $exception) {
            $this->logger->error(
                'error while node retrieval'
                , [
                    'userId'    => $userId,
                    'exception' => $exception
                ]
            );
            $this->writeInfo('error with retrieving node', $output);
            return IKeestashCommand::RETURN_CODE_NOT_RAN_SUCCESSFUL;
        }

        $results = [];
        $this->work($root, $output, $results);

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Name', 'Password', 'Type'])
            ->setRows($results);
        $table->render();

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function work(
        Folder            $folder
        , OutputInterface $output
        , array           &$results
    ): void {
        $results[] = [
            'id'     => $folder->getId()
            , 'name' => $folder->getName()
            , 'type' => $folder instanceof Root
                ? 'Root'
                : 'folder'
        ];

        /** @var Edge $edge */
        foreach ($folder->getEdges() as $edge) {
            $node = $edge->getNode();
            if ($node instanceof Folder) {

                $this->work(
                    $node
                    , $output
                    , $results
                );
            }


            $results[] = [
                'id'     => $edge->getNode()->getId()
                , 'name' => $edge->getNode()->getName()
                , 'type' => 'Credential'
            ];
        }
    }


}

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
use KSA\PasswordManager\Entity\Folder\Folder;
use KSA\PasswordManager\Entity\Node\Credential\Credential;
use KSA\PasswordManager\Exception\PasswordManagerException;
use KSA\PasswordManager\Repository\Node\NodeRepository;
use KSA\PasswordManager\Service\NodeEncryptionService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;

class Dump extends KeestashCommand {

    public const ARGUMENT_NAME_NODE_ID     = 'nodeId';
    public const OPTION_NAME_SHOW_PASSWORD = 'show-password';

    private NodeRepository        $nodeRepository;
    private LoggerInterface               $logger;
    private NodeEncryptionService $nodeEncryptionService;

    public function __construct(
        NodeRepository          $nodeRepository
        , LoggerInterface               $logger
        , NodeEncryptionService $nodeEncryptionService
    ) {
        parent::__construct();

        $this->nodeRepository        = $nodeRepository;
        $this->logger                = $logger;
        $this->nodeEncryptionService = $nodeEncryptionService;
    }

    protected function configure(): void {
        $this->setName("password-manager:dump")
            ->setDescription("show an node")
            ->addArgument(
                Dump::ARGUMENT_NAME_NODE_ID
                , InputArgument::REQUIRED
                , 'The node to dump'
            )
            ->addOption(
                Dump::OPTION_NAME_SHOW_PASSWORD
                , 's'
                , InputOption::VALUE_NONE
                , 'whether the password should be shown for a credential node'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $nodeId       = $input->getArgument(Dump::ARGUMENT_NAME_NODE_ID);
        $showPassword = (bool) $input->getOption(Dump::OPTION_NAME_SHOW_PASSWORD);
        try {
            $node = $this->nodeRepository->getNode((int) $nodeId, 0, 1);
        } catch (PasswordManagerException $exception) {
            $this->logger->error(
                'error while node retrieval'
                , [
                    'nodeId'    => $nodeId,
                    'exception' => $exception
                ]
            );
            $this->writeInfo('error with retrieving node', $output);
            return 0;
        }

        if ($node instanceof Credential) {
            $this->dumpCredential($node, $input, $output, $showPassword);
        } else if ($node instanceof Folder) {
            $this->dumpFolder($node, $output);
        } else {
            throw new PasswordManagerException('unknown node type for ' . $nodeId);
        }
        return 0;
    }

    private function dumpCredential(
        Credential        $credential
        , InputInterface  $input
        , OutputInterface $output
        , bool            $showPassword = false
    ): void {

        if (true === $showPassword) {
            $helper   = $this->getHelper('question');
            $question = new ConfirmationQuestion('!! Warning !! Do you want to show this sensitive data?', false);

            if (!$helper->ask($input, $output, $question)) {
                $showPassword = false;
            }
        }

        if (true === $showPassword) {
            $this->nodeEncryptionService->decryptNode($credential);
        }

        $this->writeInfo(
            "\n" .
            json_encode(
                [
                    'id'         => $credential->getId()
                    , 'name'     => $credential->getName()
                    , 'password' => $showPassword
                    ? $credential->getPassword()->getPlain()
                    : 'censored'
                ]
                , JSON_PRETTY_PRINT
            )
            , $output
        );

    }

    private function dumpFolder(Folder $folder, OutputInterface $output): void {
        $this->writeInfo(
            "\n" .
            json_encode(
                [
                    'id'     => $folder->getId()
                    , 'name' => $folder->getName()
                ]
                , JSON_PRETTY_PRINT
            )
            , $output
        );
    }

}
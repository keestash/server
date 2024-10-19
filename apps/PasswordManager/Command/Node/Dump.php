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
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

final class Dump extends KeestashCommand {

    public const string ARGUMENT_NAME_NODE_ID = 'nodeId';

    public function __construct(
        private readonly NodeRepository    $nodeRepository
        , private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("password-manager:dump")
            ->setDescription("show an node")
            ->addArgument(
                Dump::ARGUMENT_NAME_NODE_ID
                , InputArgument::REQUIRED
                , 'The node to dump'
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $nodeId = $input->getArgument(Dump::ARGUMENT_NAME_NODE_ID);
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
            $this->dumpCredential($node, $output);
        } else if ($node instanceof Folder) {
            $this->dumpFolder($node, $output);
        } else {
            throw new PasswordManagerException('unknown node type for ' . $nodeId);
        }
        return 0;
    }

    private function dumpCredential(Credential $credential, OutputInterface $output): void {

        $this->writeInfo(
            "\n" .
            json_encode(
                [
                    'id'     => $credential->getId()
                    , 'name' => $credential->getName()
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

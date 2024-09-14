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

namespace Keestash\Command\Worker\Queue;

use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Service\Queue\IQueueService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Reset extends KeestashCommand {

    public const ARGUMENT_NAME_UUID = 'uuid';

    public function __construct(private readonly IQueueService $queueService) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("worker:queue:reset")
            ->setDescription("resets attempts of a single message or all, if no id is given")
            ->addArgument(
                QueueDelete::ARGUMENT_NAME_UUID
                , InputArgument::IS_ARRAY | InputArgument::OPTIONAL
                , 'the uuid to update'
            );
    }


    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $uuid   = (array) $input->getArgument(Reset::ARGUMENT_NAME_UUID);
        $answer = true;

        if (0 === count($uuid)) {
            $answer = $this->confirmQuestion(
                'do you really want to reset all queue entries?'
                , $input
                , $output
            );
            $uuid   = $this->queueService->getQueue(true);
        }

        if (false === $answer) {
            $this->writeInfo('stopping', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        foreach ($uuid as $u) {
            if ($u instanceof IMessage) {
                $u =  $u->getId();
            }
            $this->writeInfo('reseting ' . $u, $output);
            $this->queueService->updateAttempts((string) $u, 0);
        }

        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
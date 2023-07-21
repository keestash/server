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

use doganoo\DI\DateTime\IDateTimeService;
use Keestash\Command\KeestashCommand;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\Service\Queue\IQueueService;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class QueueList extends KeestashCommand {

    private IQueueService    $queueService;
    private IDateTimeService $dateTimeService;

    public function __construct(
        IQueueService      $queueService
        , IDateTimeService $dateTimeService
    ) {
        parent::__construct();
        $this->queueService    = $queueService;
        $this->dateTimeService = $dateTimeService;
    }

    protected function configure(): void {
        $this->setName("worker:queue:list")
            ->setDescription("lists the current queue entries");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $queue = $this->queueService->getQueue(true);

        $tableRows = [];
        /** @var IMessage $message */
        foreach ($queue as $message) {
            $tableRows[] = [
                $message->getId()
                , $this->dateTimeService->toDMYHIS($message->getCreateTs())
                , $message->getPriority()
                , $message->getAttempts()
                , $this->dateTimeService->toDMYHIS($message->getReservedTs())
                , json_encode($message->getPayload())
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'CreateTs', 'Priority', 'Attempts', 'ReservedTs'])
            ->setRows($tableRows);
        $table->render();

        $this->writeInfo(sprintf('number of entries in queue: %s', $queue->length()), $output);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}
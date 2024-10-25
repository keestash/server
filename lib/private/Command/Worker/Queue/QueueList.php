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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class QueueList extends KeestashCommand {

    private const string  OPTION_NAME_SHOW_PAYLOAD = 'show-payload';

    public function __construct(
        private readonly IQueueService      $queueService
        , private readonly IDateTimeService $dateTimeService
    ) {
        parent::__construct();
    }

    #[\Override]
    protected function configure(): void {
        $this->setName("worker:queue:list")
            ->setDescription("lists the current queue entries")
            ->addOption(
                QueueList::OPTION_NAME_SHOW_PAYLOAD,
                'p',
                InputOption::VALUE_NONE
            );
    }

    #[\Override]
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $queue       = $this->queueService->getQueue(true);
        $showPayload = (bool) $input->getOption(QueueList::OPTION_NAME_SHOW_PAYLOAD);
        $tableRows   = [];
        /** @var IMessage $message */
        foreach ($queue as $message) {
            $tableRows[] = [
                $message->getId()
                , $message->getPayload()['listener']
                , $showPayload ? json_encode($message->getPayload()) : 'payload not showed'
                , $message->getPriority()
                , $message->getAttempts()
                , $this->dateTimeService->toDMYHIS($message->getReservedTs())
                , $this->dateTimeService->toDMYHIS($message->getCreateTs())
            ];
        }

        $table = new Table($output);
        $table
            ->setHeaders(['ID', 'Listener', 'Payload', 'Priority', 'Attempts', 'ReservedTs', 'CreateTs'])
            ->setRows($tableRows);
        $table->render();

        $this->writeInfo(sprintf('number of entries in queue: %s', $queue->length()), $output);
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

}

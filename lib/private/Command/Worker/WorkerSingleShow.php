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

namespace Keestash\Command\Worker;

use JsonException;
use Keestash\Command\KeestashCommand;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\Service\Queue\IQueueService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class WorkerSingleShow extends KeestashCommand {

    public const OPTION_NAME_UUID = 'uuid';

    public function __construct(
        private readonly IQueueService     $queueService
        , private readonly LoggerInterface $logger
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        parent::configure();
        $this->setName("worker:single:show")
            ->setDescription("runs a single job in the queue")
            ->addOption(
                WorkerSingleShow::OPTION_NAME_UUID
                , 'u'
                , InputOption::VALUE_REQUIRED
                , 'the queue uuid to execute'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $uuid    = (string) $input->getOption(WorkerSingleShow::OPTION_NAME_UUID);
        $message = null;

        try {
            $message = $this->queueService->getByUuid($uuid);
        } catch (JsonException|NoRowsFoundException $e) {
            $this->logger->info('error with retrieving job', ['exception' => $e]);
            $this->writeError('error with retrieving job', $output);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        $tableRows   = [];
        $tableRows[] = [
            $message->getId(),
            $message->getPayload()['listener'] ?? 'no listener',
            $message->getAttempts(),
            unserialize($message->getPayload()['event']['serialized'])
        ];
        $table       = new Table($output);
        $table
            ->setHeaders(['ID', 'ScheduleUserStateEventListenerListener', 'Attempts'])
            ->setRows($tableRows);
        $table->render();


        return 0;
    }

}
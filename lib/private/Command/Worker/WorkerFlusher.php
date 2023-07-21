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

use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Queue\Result;
use Keestash\Exception\KeestashException;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Core\Data\IDataService;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Queue\Handler\IEventHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WorkerFlusher extends KeestashCommand {

    public const OPTION_NAME_FORCE = 'force';

    public function __construct(
        private readonly IQueueService      $queueService
        , private readonly LoggerInterface  $logger
        , private readonly IQueueRepository $queueRepository
        , private readonly IEventHandler    $eventHandler
        , private readonly IDataService     $dataService
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        parent::configure();
        $this->setName("worker:flush")
            ->setDescription("flushes the queue/executes all jobs")
            ->addOption(
                WorkerFlusher::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to ignore the lock'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $force         = (bool) $input->getOption(WorkerFlusher::OPTION_NAME_FORCE);
        $workerLogFile = $this->dataService->getPath() . '/' . WorkerRunner::WORKER_LOG_FILE;
        $queue         = $this->queueService->getQueue();

        if (0 === $queue->length()) {
            $this->writeInfo('no jobs found. Terminating', $output);
            $this->logger->info('no jobs found. Terminating', ['context' => 'flusher']);
            return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
        }

        if (true === is_file($workerLogFile) && false === $force) {
            $response = $this->askQuestion('the worker is actually locked. Do you still want to continue?', $input, $output);
            if ($response !== 'y') {
                $this->writeInfo('terminating', $output);
                return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
            }
        }

        /** @var IMessage $message */
        foreach ($queue as $message) {
            $this->writeInfo('processing ' . $message->getId(), $output);
            if ($message->getAttempts() > 3) {
                continue;
            }

            $result = Result::getNotOk();

            try {
                $this->logger->debug('handling an event message', ['message' => $message, 'context' => 'flusher']);
                $result = $this->eventHandler->handle($message);
            } catch (Throwable $exception) {
                $this->logger->error('error processing message', ['exception' => $exception, 'context' => 'flusher']);
                $this->writeError('error while processing ' . $message->getId(), $output);
            }

            switch ($result->getCode()) {
                case IResult::RETURN_CODE_OK:
                    $this->queueRepository->delete($message);
                    break;
                case IResult::RETURN_CODE_NOT_OK:
                    $this->updateAttempts($message);
                    break;
                default:
                    throw new KeestashException();
            }
            $this->writeInfo('ended successfully', $output);

        }
        return 0;
    }

    private function updateAttempts(IMessage $message): void {
        $this->queueRepository->updateAttempts(
            $message->getId()
            , $message->getAttempts() + 1
        );
    }

}
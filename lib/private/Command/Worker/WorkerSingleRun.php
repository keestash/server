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
use Keestash\Core\DTO\Queue\Result;
use Keestash\Exception\KeestashException;
use Keestash\Exception\Repository\NoRowsFoundException;
use KSP\Command\IKeestashCommand;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Core\Data\IDataService;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Queue\Handler\IEventHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WorkerSingleRun extends KeestashCommand {

    public const OPTION_NAME_UUID  = 'uuid';
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
        $this->setName("worker:single:run")
            ->setDescription("runs a single job in the queue")
            ->addOption(
                WorkerSingleRun::OPTION_NAME_UUID
                , 'u'
                , InputOption::VALUE_REQUIRED
                , 'the queue uuid to execute'
            )
            ->addOption(
                WorkerSingleRun::OPTION_NAME_FORCE
                , 'f'
                , InputOption::VALUE_OPTIONAL | InputOption::VALUE_NONE
                , 'whether to ignore the lock'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $uuid          = (string) $input->getOption(WorkerSingleRun::OPTION_NAME_UUID);
        $force         = (bool) $input->getOption(WorkerSingleRun::OPTION_NAME_FORCE);
        $workerLogFile = $this->dataService->getPath() . '/' . WorkerRunner::WORKER_LOG_FILE;
        $message       = null;

        if (true === is_file($workerLogFile) && false === $force) {
            $response = $this->askQuestion('the worker is actually locked. Do you still want to continue?', $input, $output);
            if ($response !== 'y') {
                $this->writeInfo('terminating', $output);
                return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
            }
        }

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
            $message->getAttempts()
        ];
        $table       = new Table($output);
        $table
            ->setHeaders(['ID', 'ScheduleUserStateEventListenerListener', 'Attempts'])
            ->setRows($tableRows);
        $table->render();

        if ($message->getAttempts() > 3 && false === $force) {
            $response = $this->askQuestion(
                sprintf('the job ran %s times. Do you want to run it?', $message->getAttempts())
                , $input
                , $output
            );
            if ($response !== 'y') {
                $this->writeInfo('terminating', $output);
                return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
            }
        }

        $result = Result::getNotOk();

        try {
            $this->logger->debug('handling an event message', ['message' => $message, 'context' => 'flusher']);
            $result = $this->eventHandler->handle($message);
        } catch (Throwable $exception) {
            $this->logger->error('error processing message', ['exception' => $exception, 'context' => 'flusher']);
            $this->writeError('error while processing ' . $message->getId(), $output);
            $this->writeComment($exception->getMessage(), $output);
        }

        switch ($result->getCode()) {
            case IResult::RETURN_CODE_OK:
                $this->queueRepository->delete($message);
                $this->writeInfo('ended successfully', $output);
                break;
            case IResult::RETURN_CODE_NOT_OK:
                $this->updateAttempts($message);
                $this->writeError('ended with an error', $output);
                break;
            default:
                throw new KeestashException();
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
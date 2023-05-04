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

namespace Keestash\Command\Keestash\Worker;

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
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class WorkerRunner extends KeestashCommand {

    public const WORKER_LOG_FILE = 'file.log.worker.json';

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
        $this->setName("keestash:worker:run")
            ->setDescription("runs the keestash daemon");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $workerLogFile = $this->dataService->getPath() . '/' . WorkerRunner::WORKER_LOG_FILE;
        while (true) {
            $queue = $this->queueService->getQueue();

            if (0 === $queue->length() || true === is_file($workerLogFile)) {
                usleep(500000);
                continue;
            }

            /** @var IMessage $message */
            foreach ($queue as $message) {
                $this->writeInfo('processing ' . $message->getId(), $output);
                if ($message->getAttempts() > 3) {
                    continue;
                }

                $result = Result::getNotOk();

                try {
                    $this->logger->debug('handling an event message', ['message' => $message, 'context' => 'runner']);
                    $result = $this->eventHandler->handle($message);
                } catch (Throwable $exception) {
                    $this->logger->error('error processing message', ['exception' => $exception, 'context' => 'runner']);
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
        }
        return IKeestashCommand::RETURN_CODE_RAN_SUCCESSFUL;
    }

    private function updateAttempts(IMessage $message): void {
        $this->queueRepository->updateAttempts(
            $message->getId()
            , $message->getAttempts() + 1
        );
    }

}
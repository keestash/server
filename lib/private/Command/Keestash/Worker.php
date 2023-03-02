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

namespace Keestash\Command\Keestash;

use Keestash\Command\KeestashCommand;
use Keestash\Core\DTO\Queue\Result;
use Keestash\Exception\KeestashException;
use KSP\Core\DTO\Queue\IMessage;
use KSP\Core\DTO\Queue\IResult;
use KSP\Core\Repository\Queue\IQueueRepository;
use KSP\Core\Service\Queue\IQueueService;
use KSP\Queue\Handler\IEventHandler;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class Worker extends KeestashCommand {

    private IQueueService    $queueService;
    private LoggerInterface  $logger;
    private IQueueRepository $queueRepository;
    private IEventHandler    $eventHandler;

    public function __construct(
        IQueueService      $queueService
        , LoggerInterface  $logger
        , IQueueRepository $queueRepository
        , IEventHandler    $eventHandler
    ) {
        parent::__construct();

        $this->queueService    = $queueService;
        $this->logger          = $logger;
        $this->queueRepository = $queueRepository;
        $this->eventHandler    = $eventHandler;
    }

    protected function configure(): void {
        $this->setName("keestash:worker:run")
            ->setDescription("runs the keestash daemon");
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $execute = true;
        while (true || $execute) {
            $queue = $this->queueService->getQueue();

            if (0 === $queue->length()) {
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
                    $this->logger->debug('handling an event message', ['message' => $message]);
                    $result = $this->eventHandler->handle($message);
                } catch (Throwable $exception) {
                    $this->logger->error('error processing message', ['exception' => $exception]);
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
        return 0;
    }

    private function updateAttempts(IMessage $message): void {
        $this->queueRepository->updateAttempts(
            $message->getId()
            , $message->getAttempts() + 1
        );
    }

}